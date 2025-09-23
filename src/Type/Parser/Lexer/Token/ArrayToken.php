<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayExpectedCommaOrClosingBracket;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayMissingSubType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayColonTokenMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayEmptyElements;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayInvalidUnsealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayUnexpectedTokenAfterSealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayWithoutElementsWithSealedType;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;

/** @internal */
final class ArrayToken implements TraversingToken
{
    private static self $array;

    private static self $nonEmptyArray;

    private static self $iterable;

    private function __construct(
        /** @var class-string<ArrayType|NonEmptyArrayType|IterableType> */
        private string $arrayType,
        private string $symbol
    ) {}

    public static function array(): self
    {
        return self::$array ??= new self(ArrayType::class, 'array');
    }

    public static function nonEmptyArray(): self
    {
        return self::$nonEmptyArray ??= new self(NonEmptyArrayType::class, 'non-empty-array');
    }

    public static function iterable(): self
    {
        return self::$iterable ??= new self(IterableType::class, 'iterable');
    }

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done()) {
            return ($this->arrayType)::native();
        }

        if ($stream->next() instanceof OpeningBracketToken) {
            $stream->forward();

            return $this->arrayType($stream);
        }

        if ($this->arrayType === ArrayType::class && $stream->next() instanceof OpeningCurlyBracketToken) {
            return $this->shapedArrayType($stream);
        }

        return ($this->arrayType)::native();
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    private function arrayType(TokenStream $stream): CompositeTraversableType
    {
        if ($stream->done()) {
            throw new ArrayMissingSubType($this->symbol . '<');
        }

        $type = $stream->read();

        if ($stream->done()) {
            throw new ArrayExpectedCommaOrClosingBracket($this->symbol, $type);
        }

        $token = $stream->forward();

        if ($token instanceof ClosingBracketToken) {
            return new ($this->arrayType)(ArrayKeyType::default(), $type);
        }

        if (! $token instanceof CommaToken) {
            throw new ArrayCommaMissing($this->symbol, $type);
        }

        $keyType = ArrayKeyType::from($type);

        if ($stream->done()) {
            throw new ArrayMissingSubType($this->symbol . '<' . $keyType->toString() . ',');
        }

        $subType = $stream->read();

        $arrayType = new ($this->arrayType)($keyType, $subType);

        if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
            throw new ArrayClosingBracketMissing($arrayType);
        }

        return $arrayType;
    }

    private function shapedArrayType(TokenStream $stream): ShapedArrayType
    {
        $stream->forward();

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
                throw new ShapedArrayCommaMissing($elements);
            }

            if ($stream->done()) {
                throw new ShapedArrayClosingBracketMissing($elements);
            }

            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            $optional = false;

            if ($stream->next() instanceof TripleDotsToken) {
                $isUnsealed = true;
                $stream->forward();
            }

            if ($stream->done()) {
                throw new ShapedArrayClosingBracketMissing($elements, unsealedType: false);
            }

            if ($stream->next() instanceof VacantToken) {
                $type = new StringValueType($stream->forward()->symbol());
            } elseif ($isUnsealed && ($stream->next() instanceof ClosingCurlyBracketToken || $stream->next() instanceof CommaToken)) {
                $stream->forward();
                break;
            } else {
                $type = $stream->read();
            }

            if ($isUnsealed) {
                $unsealedType = $type;

                if ($elements === []) {
                    throw new ShapedArrayWithoutElementsWithSealedType($unsealedType);
                }

                if (! $unsealedType instanceof ArrayType) {
                    throw new ShapedArrayInvalidUnsealedType($elements, $unsealedType);
                }

                if ($stream->done()) {
                    throw new ShapedArrayClosingBracketMissing($elements, $unsealedType);
                } elseif (! $stream->next() instanceof ClosingCurlyBracketToken) {
                    $unexpected = [];

                    while (! $stream->done() && ! $stream->next() instanceof ClosingCurlyBracketToken) {
                        $unexpected[] = $stream->forward();
                    }

                    throw new ShapedArrayUnexpectedTokenAfterSealedType($elements, $unsealedType, $unexpected);
                }

                continue;
            }

            if ($stream->done()) {
                $elements[] = new ShapedArrayElement(new IntegerValueType($index), $type);

                throw new ShapedArrayClosingBracketMissing($elements);
            }

            if ($stream->next() instanceof NullableToken) {
                $stream->forward();
                $optional = true;

                if ($stream->done()) {
                    throw new ShapedArrayColonTokenMissing($elements, $type);
                }
            }

            if ($stream->next() instanceof ColonToken) {
                $stream->forward();

                $key = $type;
                $type = null;

                if (! $key instanceof StringValueType && ! $key instanceof IntegerValueType) {
                    $key = new StringValueType($key->toString());
                }

                if ($key instanceof IntegerValueType) {
                    $index++;
                }
            } else {
                if ($optional) {
                    throw new ShapedArrayColonTokenMissing($elements, $type);
                }

                $key = new IntegerValueType($index++);
            }

            if (! $type) {
                if ($stream->done()) {
                    throw new ShapedArrayElementTypeMissing($elements, $key, $optional);
                }

                $type = $stream->read();
            }

            $elements[] = new ShapedArrayElement($key, $type, $optional);

            if ($stream->done()) {
                throw new ShapedArrayClosingBracketMissing($elements);
            }
        }

        if ($elements === []) {
            throw new ShapedArrayEmptyElements();
        }

        if ($unsealedType) {
            return ShapedArrayType::unsealed($unsealedType, ...$elements);
        } elseif ($isUnsealed) {
            return ShapedArrayType::unsealedWithoutType(...$elements);
        }

        return ShapedArrayType::from(...$elements);
    }
}
