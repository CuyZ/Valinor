<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\BooleanType;
use CuyZ\Valinor\Type\Types\FloatType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;

use function strtolower;

/** @internal */
final class NativeToken implements TraversingToken
{
    /** @var array<string, self> */
    private static array $map = [];

    private Type $type;

    private function __construct(Type $type)
    {
        $this->type = $type;
    }

    public static function accepts(string $symbol): bool
    {
        return (bool)self::type($symbol);
    }

    public static function from(string $symbol): self
    {
        $type = self::type($symbol);

        assert($type instanceof Type);

        return self::$map[$symbol] ??= new self($type);
    }

    public function traverse(TokenStream $stream): Type
    {
        return $this->type;
    }

    private static function type(string $symbol): ?Type
    {
        // @PHP8.0 match
        switch (strtolower($symbol)) {
            case 'null':
                return NullType::get();
            case 'mixed':
                return MixedType::get();
            case 'float':
                return FloatType::get();
            case 'positive-int':
                return PositiveIntegerType::get();
            case 'negative-int':
                return NegativeIntegerType::get();
            case 'string':
                return NativeStringType::get();
            case 'non-empty-string':
                return NonEmptyStringType::get();
            case 'bool':
            case 'boolean':
                return BooleanType::get();
            case 'array-key':
                return ArrayKeyType::default();
            case 'object':
                return UndefinedObjectType::get();
        }

        return null;
    }
}
