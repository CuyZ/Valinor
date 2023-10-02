<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;

/** @internal */
final class ArrayToken extends ShapedArrayToken
{
    private static self $array;

    private static self $nonEmptyArray;

    private function __construct(
        /** @var class-string<ArrayType|NonEmptyArrayType> */
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

    public function symbol(): string
    {
        return $this->symbol;
    }

    private function arrayType(TokenStream $stream): CompositeTraversableType
    {
        $stream->forward();
        $type = $stream->read();
        $token = $stream->forward();

        if ($token instanceof ClosingBracketToken) {
            return new ($this->arrayType)(ArrayKeyType::default(), $type);
        }

        if (! $token instanceof CommaToken) {
            throw new ArrayCommaMissing($this->arrayType, $type);
        }

        $keyType = ArrayKeyType::from($type);
        $subType = $stream->read();
        $arrayType = new ($this->arrayType)($keyType, $subType);

        if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
            throw new ArrayClosingBracketMissing($arrayType);
        }

        return $arrayType;
    }
}
