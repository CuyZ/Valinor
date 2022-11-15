<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\NumericStringType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;

use function strtolower;

/** @internal */
final class NativeToken implements TraversingToken
{
    /** @var array<string, self> */
    private static array $map = [];

    private Type $type;

    private string $symbol;

    private function __construct(Type $type, string $symbol)
    {
        $this->type = $type;
        $this->symbol = $symbol;
    }

    public static function accepts(string $symbol): bool
    {
        return (bool)self::type(strtolower($symbol));
    }

    public static function from(string $symbol): self
    {
        $symbol = strtolower($symbol);
        $type = self::type($symbol);

        assert($type instanceof Type);

        return self::$map[$symbol] ??= new self($type, $symbol);
    }

    public function traverse(TokenStream $stream): Type
    {
        return $this->type;
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    private static function type(string $symbol): ?Type
    {
        // PHP8.0 match
        switch ($symbol) {
            case 'null':
                return NullType::get();
            case 'true':
                return BooleanValueType::true();
            case 'false':
                return BooleanValueType::false();
            case 'mixed':
                return MixedType::get();
            case 'float':
                return NativeFloatType::get();
            case 'positive-int':
                return PositiveIntegerType::get();
            case 'negative-int':
                return NegativeIntegerType::get();
            case 'string':
                return NativeStringType::get();
            case 'non-empty-string':
                return NonEmptyStringType::get();
            case 'numeric-string':
                return NumericStringType::get();
            case 'bool':
            case 'boolean':
                return NativeBooleanType::get();
            case 'array-key':
                return ArrayKeyType::default();
            case 'object':
                return UndefinedObjectType::get();
        }

        return null;
    }
}
