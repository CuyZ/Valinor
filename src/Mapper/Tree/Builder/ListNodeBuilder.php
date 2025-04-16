<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\InvalidIterableKeyType;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidListKey;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceIsEmptyList;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;

use function assert;
use function is_int;
use function is_iterable;
use function is_string;

/** @internal */
final class ListNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ListType || $type instanceof NonEmptyListType);

        if ($shell->allowUndefinedValues() && $value === null) {
            return Node::new(value: []);
        }

        if (! is_iterable($value)) {
            return Node::error($shell, new SourceMustBeIterable($value, $type));
        }

        if ($value === [] && $type instanceof NonEmptyListType) {
            return Node::error($shell, new SourceIsEmptyList($type));
        }

        $subType = $type->subType();

        $expected = 0;
        $children = [];
        $errors = [];

        foreach ($value as $key => $val) {
            if (! is_string($key) && ! is_int($key)) {
                throw new InvalidIterableKeyType($key, $shell->path());
            }

            if ($shell->allowNonSequentialList() || $key === $expected) {
                $child = $shell->child((string)$expected, $subType);
                $childNode = $children[$expected] = $rootBuilder->build($child->withValue($val));
            } else {
                $child = $shell->child((string)$key, $subType);
                $childNode = $children[$key] = Node::error($child, new InvalidListKey($key, $expected));
            }

            if (! $childNode->isValid()) {
                $errors[] = $childNode;
            }

            $expected++;
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
