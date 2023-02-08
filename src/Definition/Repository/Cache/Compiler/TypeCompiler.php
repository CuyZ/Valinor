<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception\TypeCannotBeCompiled;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\FloatValueType;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\NumericStringType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use UnitEnum;

use function array_keys;
use function array_map;
use function implode;
use function var_export;

/** @internal */
final class TypeCompiler
{
    public function compile(Type $type): string
    {
        $class = $type::class;

        switch (true) {
            case $type instanceof NullType:
            case $type instanceof NativeBooleanType:
            case $type instanceof NativeFloatType:
            case $type instanceof NativeIntegerType:
            case $type instanceof PositiveIntegerType:
            case $type instanceof NegativeIntegerType:
            case $type instanceof NativeStringType:
            case $type instanceof NonEmptyStringType:
            case $type instanceof NumericStringType:
            case $type instanceof UndefinedObjectType:
            case $type instanceof MixedType:
                return "$class::get()";
            case $type instanceof BooleanValueType:
                return $type->value() === true
                    ? "$class::true()"
                    : "$class::false()";
            case $type instanceof IntegerRangeType:
                return "new $class({$type->min()}, {$type->max()})";
            case $type instanceof StringValueType:
            case $type instanceof IntegerValueType:
            case $type instanceof FloatValueType:
                $value = var_export($type->value(), true);

                return "new $class($value)";
            case $type instanceof IntersectionType:
            case $type instanceof UnionType:
                $subTypes = array_map(
                    fn (Type $subType) => $this->compile($subType),
                    $type->types()
                );

                return "new $class(" . implode(', ', $subTypes) . ')';
            case $type instanceof ArrayKeyType:
                return match ($type->toString()) {
                    'string' => "$class::string()",
                    'int' => "$class::integer()",
                    default => "$class::default()",
                };
            case $type instanceof ShapedArrayType:
                $shapes = array_map(
                    fn (ShapedArrayElement $element) => $this->compileArrayShapeElement($element),
                    $type->elements()
                );
                $shapes = implode(', ', $shapes);

                return "new $class(...[$shapes])";
            case $type instanceof ArrayType:
            case $type instanceof NonEmptyArrayType:
                if ($type->toString() === 'array' || $type->toString() === 'non-empty-array') {
                    return "$class::native()";
                }

                $keyType = $this->compile($type->keyType());
                $subType = $this->compile($type->subType());

                return "new $class($keyType, $subType)";
            case $type instanceof ListType:
            case $type instanceof NonEmptyListType:
                if ($type->toString() === 'list' || $type->toString() === 'non-empty-list') {
                    return "$class::native()";
                }

                $subType = $this->compile($type->subType());

                return "new $class($subType)";
            case $type instanceof IterableType:
                $keyType = $this->compile($type->keyType());
                $subType = $this->compile($type->subType());

                return "new $class($keyType, $subType)";
            case $type instanceof NativeClassType:
            case $type instanceof InterfaceType:
                $generics = [];

                foreach ($type->generics() as $key => $generic) {
                    $generics[] = var_export($key, true) . ' => ' . $this->compile($generic);
                }

                $generics = implode(', ', $generics);

                if ($type instanceof InterfaceType) {
                    return "new $class('{$type->className()}', [$generics])";
                }

                $parent = $type->hasParent()
                    ? $this->compile($type->parent())
                    : 'null';

                return "new $class('{$type->className()}', [$generics], $parent)";
            case $type instanceof ClassStringType:
                if (null === $type->subType()) {
                    return "new $class()";
                }

                $subType = $this->compile($type->subType());

                return "new $class($subType)";
            case $type instanceof EnumType:
                $enumName = var_export($type->className(), true);
                $pattern = var_export($type->pattern(), true);

                $cases = array_map(
                    fn (string|int $key, UnitEnum $case) => var_export($key, true) . ' => ' . var_export($case, true),
                    array_keys($type->cases()),
                    $type->cases()
                );
                $cases = implode(', ', $cases);

                return "new $class($enumName, $pattern, [$cases])";
            case $type instanceof UnresolvableType:
                $raw = var_export($type->toString(), true);
                $message = var_export($type->getMessage(), true);

                return "new $class($raw, $message)";
            default:
                throw new TypeCannotBeCompiled($type);
        }
    }

    private function compileArrayShapeElement(ShapedArrayElement $element): string
    {
        $class = ShapedArrayElement::class;
        $key = $this->compile($element->key());
        $type = $this->compile($element->type());
        $optional = var_export($element->isOptional(), true);

        return "new $class($key, $type, $optional)";
    }
}
