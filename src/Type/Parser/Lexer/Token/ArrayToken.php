<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayExpectedCommaOrClosingBracket;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayMissingSubType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayColonTokenMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayUnexpectedTokenAfterSealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayWithoutElementsWithSealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListMixedKeys;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\ShapedListType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Polyfill;

use function array_map;
use function implode;

/** @internal */
final class ArrayToken implements TraversingToken
{
    private bool $isList;
    private bool $canBeShaped;

    public function __construct(
        private string $symbol,
        /** @var class-string<ArrayType|NonEmptyArrayType|ListType|NonEmptyListType|IterableType> */
        private string $type,
    ) {
        $this->isList = $type === ListType::class || $type === NonEmptyListType::class;
        $this->canBeShaped = $type === ArrayType::class || $type === ListType::class;
    }

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done()) {
            return ($this->type)::native();
        }

        if ($stream->next() instanceof OpeningBracketToken) {
            $stream->forward();

            return $this->arrayType($stream);
        }

        if ($this->canBeShaped && $stream->next() instanceof OpeningCurlyBracketToken) {
            return $this->shapedType($stream);
        }

        return ($this->type)::native();
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    private function arrayType(TokenStream $stream): Type
    {
        if ($stream->done()) {
            throw new ArrayMissingSubType($this->symbol . '<');
        }

        $type = $stream->read();

        if ($this->isList) {
            /** @var class-string<ListType|NonEmptyListType> $className */
            $className = $this->type;

            $listType = new $className($type);

            if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
                throw new ArrayClosingBracketMissing($listType);
            }

            return $listType;
        }

        /** @var class-string<ArrayType|NonEmptyArrayType|IterableType> $className */
        $className = $this->type;

        if ($stream->done()) {
            throw new ArrayExpectedCommaOrClosingBracket($this->symbol, $type);
        }

        $token = $stream->forward();

        if ($token instanceof ClosingBracketToken) {
            return new $className(ArrayKeyType::default(), $type);
        }

        if (! $token instanceof CommaToken) {
            throw new ArrayCommaMissing($this->symbol, $type);
        }

        $keyType = $type;

        if (! $keyType instanceof ArrayKeyType) {
            $keyTypes = $type instanceof UnionType ? $type->types() : [$type];

            try {
                $keyType = ArrayKeyType::from($keyTypes);
            } catch (InvalidType $exception) {
                $subTypes = implode('|', array_map(static fn (Type $type) => $type->toString(), $keyTypes));

                $keyType = new UnresolvableType($subTypes, $exception->getMessage());
            }
        } else {
            $keyTypes = $keyType->types;
        }

        if ($stream->done()) {
            throw new ArrayMissingSubType($this->symbol . '<' . $keyType->toString() . ',');
        }

        $subType = $stream->read();

        if ($keyType instanceof UnresolvableType) {
            $arrayType = $keyType->forArrayType($this->symbol, $keyTypes, $subType);
        } else {
            $arrayType = new $className($keyType, $subType);
        }

        if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
            throw new ArrayClosingBracketMissing($arrayType);
        }

        return $arrayType;
    }

    private function shapedType(TokenStream $stream): Type
    {
        $stream->forward();

        $elements = [];
        $index = 0;
        $unsealedType = null;
        $hasExplicitKeys = null;

        while (! $stream->done()) {
            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            if (! empty($elements) && ! $stream->forward() instanceof CommaToken) {
                throw new ShapedArrayCommaMissing($this->signature($elements));
            }

            if ($stream->done()) {
                throw new ShapedArrayClosingBracketMissing($this->signature($elements));
            }

            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            $keyToken = $stream->next();

            if ($keyToken instanceof TripleDotsToken) {
                $stream->forward();

                if ($stream->done()) {
                    throw new ShapedArrayClosingBracketMissing($this->signature($elements), unsealedType: false);
                }

                $next = $stream->next();

                $unsealedType = $this->isList ? ListType::native() : ArrayType::native();

                if ($next instanceof CommaToken) {
                    $stream->forward();
                } elseif ($next instanceof ClosingCurlyBracketToken) {
                    $stream->forward();

                    break;
                } else {
                    if ($next instanceof OpeningBracketToken) {
                        $stream->forward();

                        if ($this->isList && $stream->done()) {
                            throw new ShapedArrayClosingBracketMissing($this->signature($elements), unsealedType: false);
                        }

                        $unsealedType = $this->arrayType($stream);
                    } else {
                        $unsealedType = $stream->read();

                        if ($this->isList && $stream->done()) {
                            $elements[] = new ShapedArrayElement(new IntegerValueType($index), $unsealedType);

                            throw new ShapedArrayClosingBracketMissing($this->signature($elements));
                        }
                    }

                    if ($elements === []) {
                        throw new ShapedArrayWithoutElementsWithSealedType($this->signature($elements), $unsealedType);
                    }
                }

                if ($stream->done()) {
                    throw new ShapedArrayClosingBracketMissing($this->signature($elements), $unsealedType);
                } elseif (! $stream->next() instanceof ClosingCurlyBracketToken) {
                    $unexpected = [];

                    while (! $stream->done() && ! $stream->next() instanceof ClosingCurlyBracketToken) {
                        $unexpected[] = $stream->forward();
                    }

                    throw new ShapedArrayUnexpectedTokenAfterSealedType($this->signature($elements), $unsealedType, $unexpected);
                }

                continue;
            }

            $type = $stream->read();

            if ($stream->done()) {
                $elements[] = new ShapedArrayElement(new IntegerValueType($index), $type);

                throw new ShapedArrayClosingBracketMissing($this->signature($elements));
            }

            $optional = false;
            $isExplicitKey = false;

            if ($stream->next() instanceof NullableToken) {
                $stream->forward();
                $optional = true;

                if ($stream->done()) {
                    throw new ShapedArrayColonTokenMissing($this->signature($elements), $type);
                }
            }

            if ($stream->next() instanceof ColonToken) {
                $stream->forward();

                $key = $type;
                $type = null;
                $isExplicitKey = true;

                if (! $key instanceof StringValueType && ! $key instanceof IntegerValueType) {
                    $key = new StringValueType($keyToken->symbol());
                }

                if ($key instanceof IntegerValueType) {
                    $index++;
                }
            } else {
                if ($optional) {
                    throw new ShapedArrayColonTokenMissing($this->signature($elements), $type);
                }

                $key = new IntegerValueType($index++);
            }

            if (! $type) {
                if ($stream->done()) {
                    throw new ShapedArrayElementTypeMissing($this->signature($elements), $key, $optional);
                }

                $type = $stream->read();
            }

            if ($this->isList && $hasExplicitKeys !== null && $hasExplicitKeys !== $isExplicitKey) {
                throw new ShapedListMixedKeys($elements);
            }

            $hasExplicitKeys = $isExplicitKey;

            $elements[] = new ShapedArrayElement($key, $type, $optional);

            if ($stream->done()) {
                throw new ShapedArrayClosingBracketMissing($this->signature($elements));
            }
        }

        return $this->isList
            ? ShapedListType::from($elements, $unsealedType)
            : ShapedArrayType::from($elements, $unsealedType);
    }

    /**
     * @param list<ShapedArrayElement> $elements
     */
    private function signature(array $elements): string
    {
        if (! $this->isList) {
            return 'array{' . implode(', ', array_map(
                static fn (ShapedArrayElement $element) => $element->toString(),
                $elements,
            ));
        }

        $hasOptional = Polyfill::array_any(
            $elements,
            static fn (ShapedArrayElement $element) => $element->isOptional(),
        );

        return 'list{' . implode(', ', array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $elements,
        ));
    }
}
