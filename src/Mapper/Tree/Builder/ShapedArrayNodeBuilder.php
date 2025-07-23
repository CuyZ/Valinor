<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

use function array_key_exists;
use function assert;
use function is_array;
use function is_iterable;

/** @internal */
final class ShapedArrayNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ShapedArrayType);

        if (! is_iterable($value)) {
            return Node::error($shell, new SourceMustBeIterable($value, $type));
        }

        $children = [];
        $childrenNames = [];
        $errors = [];

        if (! is_array($value)) {
            $value = iterator_to_array($value);
        }

        foreach ($type->elements() as $element) {
            $childrenNames[] = $element->key()->value();
            $key = $element->key()->value();

            $child = $shell->child((string)$key, $element->type());
            $child = $child->withAttributes($element->attributes());

            if (array_key_exists($key, $value)) {
                $child = $child->withValue($value[$key]);
            } elseif ($element->isOptional()) {
                continue;
            }

            $child = $rootBuilder->build($child);

            if (! $child->isValid()) {
                $errors[] = $child;
            } else {
                $children[$key] = $child->value();
            }

            unset($value[$key]);
        }

        if ($type->isUnsealed()) {
            $childrenNames = array_merge($childrenNames, array_keys($value));

            $unsealedShell = $shell->withType($type->unsealedType())->withValue($value);
            $unsealedNode = $rootBuilder->build($unsealedShell);

            if (! $unsealedNode->isValid()) {
                $errors[] = $unsealedNode;
            } else {
                // @phpstan-ignore assignOp.invalid (we know value is an array)
                $children += $unsealedNode->value();
            }
        }

        if ($errors === []) {
            $node = Node::new(value: $children, childrenCount: count($children));
        } else {
            $node = Node::branchWithErrors($errors);
        }

        $node = $node->checkUnexpectedKeys($shell, $childrenNames);

        return $node;
    }
}
