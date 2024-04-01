<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Library;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use LogicException;
use UnitEnum;

use function array_map;
use function implode;
use function var_export;

/** @internal */
final class TypeAcceptNode extends Node
{
    public function __construct(private Type $type) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $this->compileType($compiler, $this->type);
    }

    private function compileType(Compiler $compiler, Type $type): Compiler
    {
        return match (true) {
            $type instanceof CompositeTraversableType => $compiler->write('\is_iterable($value)'),
            $type instanceof EnumType => $this->compileEnumType($compiler, $type),
            $type instanceof FixedType => $compiler->write('$value === ' . var_export($type->value(), true)),
            $type instanceof MixedType => $compiler->write('true'),
            $type instanceof NativeBooleanType => $compiler->write('\is_bool($value)'),
            $type instanceof NativeFloatType => $compiler->write('\is_float($value)'),
            $type instanceof NativeIntegerType => $compiler->write('\is_int($value)'),
            // @todo positive int
            $type instanceof NativeStringType => $compiler->write('\is_string($value)'),
            $type instanceof NegativeIntegerType => $compiler->write('\is_string($value) && $value < 0'),
            $type instanceof NullType => $compiler->write('\is_null($value)'),
            $type instanceof ObjectType => $compiler->write("\$value instanceof ('{$type->className()}')"), // @todo anonymous class contains absolute file path
            $type instanceof ShapedArrayType => $this->compileShapedArrayType($compiler, $type),
            $type instanceof UnionType => $this->compileUnionType($compiler, $type),
            $type instanceof UndefinedObjectType => $compiler->write('\is_object($value)'),
            default => throw new LogicException("Type `{$type->toString()}` cannot be compiled."),
        };
    }

    private function compileEnumType(Compiler $compiler, EnumType $type): Compiler
    {
        $code = '$value instanceof ' . $type->className();

        if ($type->cases() !== []) {
            $code .= ' && (' . implode(
                ' || ',
                array_map(
                    fn (UnitEnum $enum) => '$value === ' . $enum::class . '::' . $enum->name,
                    $type->cases()
                )
            ) . ')';
        }

        return $compiler->write($code);
    }

    private function compileShapedArrayType(Compiler $compiler, ShapedArrayType $type): Compiler
    {
        // @todo test
        $code = implode(
            ' && ',
            array_map(
                fn (ShapedArrayElement $element) => $this->compileType($compiler->sub(), $type)->code(),
                $type->elements()
            )
        );

        return $compiler->write($code);
    }

    private function compileUnionType(Compiler $compiler, UnionType $type): Compiler
    {
        $code = implode(
            ' || ',
            array_map(
                fn (Type $type) => $this->compileType($compiler->sub(), $type)->code(),
                $type->types()
            )
        );

        return $compiler->write($code);
    }
}
