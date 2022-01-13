<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception\TypeCannotBeCompiled;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\BooleanType;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\FloatType;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_map;
use function get_class;
use function implode;
use function var_export;

/** @internal */
final class TypeCompiler
{
    public function compile(Type $type): string
    {
        $class = get_class($type);

        switch (true) {
            case $type instanceof NullType:
            case $type instanceof BooleanType:
            case $type instanceof FloatType:
            case $type instanceof NativeIntegerType:
            case $type instanceof PositiveIntegerType:
            case $type instanceof NegativeIntegerType:
            case $type instanceof NativeStringType:
            case $type instanceof NonEmptyStringType:
            case $type instanceof UndefinedObjectType:
            case $type instanceof MixedType:
                return "$class::get()";
            case $type instanceof IntegerRangeType:
                return "new $class({$type->min()}, {$type->max()})";
            case $type instanceof StringValueType:
            case $type instanceof IntegerValueType:
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
                // @PHP8.0 match
                if ((string)$type === 'string') {
                    return "$class::string()";
                }

                if ((string)$type === 'int') {
                    return "$class::integer()";
                }

                return "$class::default()";
            case $type instanceof ShapedArrayType:
                $shapes = array_map(
                    fn (ShapedArrayElement $element) => $this->compileArrayShapeElement($element),
                    $type->elements()
                );
                $shapes = implode(', ', $shapes);

                return "new $class(...[$shapes])";
            case $type instanceof ArrayType:
            case $type instanceof NonEmptyArrayType:
                if ((string)$type === 'array' || (string)$type === 'non-empty-array') {
                    return "$class::native()";
                }

                $keyType = $this->compile($type->keyType());
                $subType = $this->compile($type->subType());

                return "new $class($keyType, $subType)";
            case $type instanceof ListType:
            case $type instanceof NonEmptyListType:
                if ((string)$type === 'list' || (string)$type === 'non-empty-list') {
                    return "$class::native()";
                }

                $subType = $this->compile($type->subType());

                return "new $class($subType)";
            case $type instanceof IterableType:
                $keyType = $this->compile($type->keyType());
                $subType = $this->compile($type->subType());

                return "new $class($keyType, $subType)";
            case $type instanceof ClassType:
            case $type instanceof InterfaceType:
                $generics = [];

                foreach ($type->generics() as $key => $generic) {
                    $generics[] = var_export($key, true) . ' => ' . $this->compile($generic);
                }

                $generics = implode(', ', $generics);

                return "new $class('{$type->className()}', [$generics])";
            case $type instanceof ClassStringType:
                if (null === $type->subType()) {
                    return "new $class()";
                }

                $subType = $this->compile($type->subType());

                return "new $class($subType)";
            case $type instanceof EnumType:
                return "new $class({$type->className()}::class)";
            case $type instanceof UnresolvableType:
                return "new $class('{$type->getMessage()}')";
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
