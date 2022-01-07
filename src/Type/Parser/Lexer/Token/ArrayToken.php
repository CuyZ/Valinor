<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidArrayKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayColonTokenMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayEmptyElements;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;

/** @internal */
final class ArrayToken implements TraversingToken
{
    /** @var class-string<ArrayType|NonEmptyArrayType> */
    private string $arrayType;

    private static self $array;

    private static self $nonEmptyArray;

    /**
     * @param class-string<ArrayType|NonEmptyArrayType> $arrayType
     */
    private function __construct(string $arrayType)
    {
        $this->arrayType = $arrayType;
    }

    public static function array(): self
    {
        return self::$array ??= new self(ArrayType::class);
    }

    public static function nonEmptyArray(): self
    {
        return self::$nonEmptyArray ??= new self(NonEmptyArrayType::class);
    }

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done()) {
            return ($this->arrayType)::native();
        }

        if ($stream->next() instanceof OpeningBracketToken) {
            return $this->arrayType($stream);
        }

        if ($this->arrayType === ArrayType::class && $stream->next() instanceof OpeningCurlyBracketToken) {
            return $this->shapedArrayType($stream);
        }

        return ($this->arrayType)::native();
    }

    private function arrayType(TokenStream $stream): CompositeTraversableType
    {
        // @PHP8.0 use `new ($this->arrayType)(...)`
        $arrayClassType = $this->arrayType;
        $stream->forward();
        $type = $stream->read();
        $token = $stream->forward();

        if ($token instanceof ClosingBracketToken) {
            return new $arrayClassType(ArrayKeyType::default(), $type);
        }

        if (! $token instanceof CommaToken) {
            throw new ArrayCommaMissing($this->arrayType, $type);
        }

        $subType = $stream->read();

        if ($type instanceof ArrayKeyType) {
            $arrayType = new $arrayClassType($type, $subType);
        } elseif ($type instanceof IntegerType) {
            $arrayType = new $arrayClassType(ArrayKeyType::integer(), $subType);
        } elseif ($type instanceof StringType) {
            $arrayType = new $arrayClassType(ArrayKeyType::string(), $subType);
        } else {
            throw new InvalidArrayKey($this->arrayType, $type, $subType);
        }

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

        while (! $stream->done()) {
            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            if (! empty($elements) && ! $stream->forward() instanceof CommaToken) {
                throw new ShapedArrayCommaMissing($elements);
            }

            $optional = false;

            if ($stream->next() instanceof UnknownSymbolToken) {
                /** @var UnknownSymbolToken $token */
                $token = $stream->forward();
                $type = new StringValueType($token->symbol());
            } else {
                $type = $stream->read();
            }

            if ($stream->done()) {
                $elements[] = new ShapedArrayElement(new StringValueType((string)$index), $type);

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
                    $key = new StringValueType((string)$key);
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

        if (empty($elements)) {
            throw new ShapedArrayEmptyElements();
        }

        return new ShapedArrayType(...$elements);
    }
}
