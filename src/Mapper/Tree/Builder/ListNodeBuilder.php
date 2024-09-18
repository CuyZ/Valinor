<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\InvalidListKey;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;

use function assert;
use function is_array;

/** @internal */
final class ListNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ListType || $type instanceof NonEmptyListType);

        if ($shell->enableFlexibleCasting() && $value === null) {
            return TreeNode::branch($shell, [], []);
        }

        if (! is_array($value)) {
            throw new SourceMustBeIterable($value, $type);
        }

        $children = $this->children($type, $shell, $rootBuilder);
        $array = $this->buildArray($children);

        return TreeNode::branch($shell, $array, $children);
    }

    /**
     * @return array<TreeNode>
     */
    private function children(CompositeTraversableType $type, Shell $shell, RootNodeBuilder $rootBuilder): array
    {
        /** @var array<mixed> $values */
        $values = $shell->value();
        $subType = $type->subType();

        $expected = 0;
        $children = [];

        foreach ($values as $key => $value) {
            if ($shell->enableFlexibleCasting() || $key === $expected) {
                $child = $shell->child((string)$expected, $subType);
                $children[$expected] = $rootBuilder->build($child->withValue($value));
            } else {
                $child = $shell->child((string)$key, $subType);
                $children[$key] = TreeNode::error($child, new InvalidListKey($key, $expected));
            }

            $expected++;
        }

        return $children;
    }

    /**
     * @param array<TreeNode> $children
     * @return mixed[]|null
     */
    private function buildArray(array $children): ?array
    {
        $array = [];

        foreach ($children as $key => $child) {
            if (! $child->isValid()) {
                return null;
            }

            $array[$key] = $child->value();
        }

        return $array;
    }
}
