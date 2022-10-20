<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedShapedArrayKeys;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

use function array_key_exists;
use function array_keys;
use function assert;
use function count;
use function is_array;

/** @internal */
final class ShapedArrayNodeBuilder implements NodeBuilder
{
    public function __construct(private bool $allowSuperfluousKeys)
    {
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ShapedArrayType);

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
    private function children(ShapedArrayType $type, Shell $shell, RootNodeBuilder $rootBuilder): array
    {
        /** @var array<mixed> $value */
        $value = $shell->value();
        $elements = $type->elements();
        $children = [];

        foreach ($elements as $element) {
            $key = $element->key()->value();

            $child = $shell->child((string)$key, $element->type());

            if (array_key_exists($key, $value)) {
                $child = $child->withValue($value[$key]);
            } elseif ($element->isOptional()) {
                continue;
            }

            $children[$key] = $rootBuilder->build($child);

            unset($value[$key]);
        }

        if (! $this->allowSuperfluousKeys && count($value) > 0) {
            throw new UnexpectedShapedArrayKeys(array_keys($value), $elements);
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
