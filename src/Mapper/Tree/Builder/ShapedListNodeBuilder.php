<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\InvalidListKey;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedShapedArrayKeys;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedListType;

use function array_key_exists;
use function assert;
use function is_array;

/** @internal */
final class ShapedListNodeBuilder implements NodeBuilder
{
    public function __construct(private bool $allowSuperfluousKeys) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ShapedListType);

        if (! is_array($value)) {
            throw new SourceMustBeIterable($value, $type);
        }

        $invalidKeys = [];
        $hasExtraKeys = false;
        $children = $this->children($type, $shell, $rootBuilder, $invalidKeys, $hasExtraKeys);

        $array = $this->buildArray($children);

        $node = TreeNode::branch($shell, $array, $children);

        foreach ($invalidKeys as $invalid) {
            $node = $node->withMessage($invalid);
        }

        if (! $this->allowSuperfluousKeys
            && $hasExtraKeys
        ) {
            $node = $node->withMessage(new UnexpectedShapedArrayKeys($value, $children));
        }

        return $node;
    }

    /**
     * @param list<InvalidListKey> $invalidKeys
     * @param-out list<InvalidListKey> $invalidKeys
     * @return array<TreeNode>
     */
    private function children(ShapedListType $type, Shell $shell, RootNodeBuilder $rootBuilder, array &$invalidKeys, bool &$hasExtraKeys): array
    {
        /** @var array<mixed> $value */
        $value = $shell->value();
        $elements = $type->elements();
        $children = [];

        $expected = 0;
        foreach ($value as $key => $element) {
            if ($key === $expected) {
                if (array_key_exists($key, $elements)) {
                    $subType = $elements[$key]->type();
                } elseif ($type->extra) {
                    $subType = $type->extra;
                } else {
                    $hasExtraKeys = true;
                    continue;
                }

                $child = $shell->child((string)$expected, $subType);
                $children[$expected] = $rootBuilder->build($child->withValue($element));
            } else {
                if (array_key_exists($key, $elements)) {
                    $subType = $elements[$key]->type();
                } elseif ($type->extra) {
                    $subType = $type->extra;
                } else {
                    $invalidKeys []= new InvalidListKey($key, $expected);
                    continue;
                }

                $child = $shell->child((string)$key, $subType);
                $children[$key] = TreeNode::error($child->withValue($element), new InvalidListKey($key, $expected));
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
