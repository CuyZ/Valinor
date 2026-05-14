<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Iterable\ListClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ListMissingSubType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayUnexpectedTokenAfterSealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayWithoutElementsWithSealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListColonTokenMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListElementTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListInvalidKey;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedListType;

/** @internal */
final class ListToken implements TraversingToken
{
    private static self $list;

    private static self $nonEmptyList;

    private function __construct(
        /** @var class-string<ListType|NonEmptyListType> */
        private string $listType,
        private string $symbol
    ) {}

    public static function list(): self
    {
        return self::$list ??= new self(ListType::class, 'list');
    }

    public static function nonEmptyList(): self
    {
        return self::$nonEmptyList ??= new self(NonEmptyListType::class, 'non-empty-list');
    }

    public function traverse(TokenStream $stream): Type
    {
        if (! $stream->done() && $stream->next() instanceof OpeningBracketToken) {
            $stream->forward();

            if ($stream->done()) {
                throw new ListMissingSubType($this->symbol);
            }

            $subType = $stream->read();

            $listType = new ($this->listType)($subType);

            if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
                throw new ListClosingBracketMissing($listType);
            }

            return $listType;
        }

        if ($this->listType === ListType::class && ! $stream->done() && $stream->next() instanceof OpeningCurlyBracketToken) {
            return $this->shapedListType($stream);
        }

        return ($this->listType)::native();
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    private function shapedListType(TokenStream $stream): Type
    {
        $stream->forward();

        /** @var list<ShapedArrayElement> $elements */
        $elements = [];
        $index = 0;
        $isUnsealed = false;
        $unsealedType = null;

        while (! $stream->done()) {
            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            if (! empty($elements) && ! $stream->forward() instanceof CommaToken) {
                throw new ShapedListCommaMissing($elements);
            }

            if ($stream->done()) {
                throw new ShapedListClosingBracketMissing($elements);
            }

            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            if ($stream->next() instanceof TripleDotsToken) {
                $isUnsealed = true;
                $stream->forward();
            }

            if ($stream->done()) {
                throw new ShapedListClosingBracketMissing($elements, unsealedType: false);
            }

            if ($isUnsealed && ($stream->next() instanceof ClosingCurlyBracketToken || $stream->next() instanceof CommaToken)) {
                $stream->forward();
                break;
            }

            // Handle ...<type> shorthand for ...list<type>
            if ($isUnsealed && $stream->next() instanceof OpeningBracketToken) {
                $stream->forward(); // consume `<`

                if ($stream->done()) {
                    throw new ShapedListClosingBracketMissing($elements, unsealedType: false);
                }

                $subType = $stream->read();
                $unsealedType = new ListType($subType);

                if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
                    throw new ShapedListClosingBracketMissing($elements, $unsealedType);
                }

                if ($elements === []) {
                    throw new ShapedArrayWithoutElementsWithSealedType($unsealedType);
                }

                if ($stream->done()) {
                    throw new ShapedListClosingBracketMissing($elements, $unsealedType);
                } elseif (! $stream->next() instanceof ClosingCurlyBracketToken) {
                    $unexpected = [];

                    while (! $stream->done() && ! $stream->next() instanceof ClosingCurlyBracketToken) {
                        $unexpected[] = $stream->forward();
                    }

                    throw new ShapedArrayUnexpectedTokenAfterSealedType($elements, $unsealedType, $unexpected);
                }

                continue;
            }

            $type = $stream->read();

            if ($stream->done()) {
                $elements[] = new ShapedArrayElement(new IntegerValueType($index), $type);

                throw new ShapedListClosingBracketMissing($elements);
            }

            if ($isUnsealed) {
                $unsealedType = $type;

                if ($elements === []) {
                    throw new ShapedArrayWithoutElementsWithSealedType($unsealedType);
                }

                if ($stream->done()) {
                    throw new ShapedListClosingBracketMissing($elements, $unsealedType);
                } elseif (! $stream->next() instanceof ClosingCurlyBracketToken) {
                    $unexpected = [];

                    while (! $stream->done() && ! $stream->next() instanceof ClosingCurlyBracketToken) {
                        $unexpected[] = $stream->forward();
                    }

                    throw new ShapedArrayUnexpectedTokenAfterSealedType($elements, $unsealedType, $unexpected);
                }

                continue;
            }

            $optional = false;

            // Optional explicit key: `1?: type`
            if (! $stream->done() && $stream->next() instanceof NullableToken) {
                $stream->forward(); // consume `?`

                if ($stream->done() || ! $stream->next() instanceof ColonToken) {
                    throw new ShapedListColonTokenMissing($elements, $type);
                }

                $optional = true;
            }

            // Required or optional explicit key: `0: type` or `1?: type`
            if (! $stream->done() && $stream->next() instanceof ColonToken) {
                $stream->forward(); // consume `:`

                if (! $type instanceof IntegerValueType || $type->value() < 0 || $type->value() !== $index) {
                    throw new ShapedListInvalidKey($type, $index, ...$elements);
                }

                if ($stream->done()) {
                    throw new ShapedListElementTypeMissing($elements, new IntegerValueType($index), $optional);
                }

                $type = $stream->read();
            }

            $elements[] = new ShapedArrayElement(new IntegerValueType($index++), $type, $optional);

            if ($stream->done()) {
                throw new ShapedListClosingBracketMissing($elements);
            }
        }

        return ShapedListType::from($elements, $isUnsealed, $unsealedType);
    }
}
