<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\InvalidIterableKeyType;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidArrayKey;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceIsEmptyArray;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;

use function array_map;
use function assert;
use function count;
use function is_int;
use function is_iterable;
use function is_string;

/** @internal */
final class ArrayNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ArrayType || $type instanceof NonEmptyArrayType || $type instanceof IterableType);

        if ($shell->allowUndefinedValues() && $value === null) {
            return Node::new([]);
        }

        if (! is_iterable($value)) {
            return Node::error($shell, new SourceMustBeIterable($value, $type));
        }

        if ($value === [] && $type instanceof NonEmptyArrayType) {
            return Node::error($shell, new SourceIsEmptyArray($type));
        }

        $keyType = $type->keyType();
        $subType = $type->subType();

        $children = [];
        $errors = [];

        foreach ($value as $key => $val) {
            if (! is_string($key) && ! is_int($key)) {
                throw new InvalidIterableKeyType($key, $shell->path());
            }

            $child = $shell->child((string)$key, $subType);

            if (! $keyType->accepts($key)) {
                $children[$key] = Node::error($child, new InvalidArrayKey($key, $keyType));
            } else {
                $children[$key] = $rootBuilder->build($child->withValue($val));
            }

            if (! $children[$key]->isValid()) {
                $errors[] = $children[$key];
            }
        }

        if ($errors !== []) {
            return Node::branchWithErrors($errors);
        }

        return Node::new(
            value: array_map(
                static fn (Node $child) => $child->value(),
                $children,
            ),
            childrenCount: count($children),
        );
    }
}
