<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedArrayKeysForClass;
use CuyZ\Valinor\Mapper\Tree\Shell;

use function count;

/** @internal */
final class FilteredObjectNodeBuilder
{
    public function __construct(private bool $allowSuperfluousKeys) {}

    public function build(ObjectBuilder $builder, Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $arguments = ArgumentsValues::forClass($builder->describeArguments(), $shell->value(), $this->allowSuperfluousKeys);

        $children = $this->children($shell, $arguments, $rootBuilder);

        $object = $this->buildObject($builder, $children);

        $node = $arguments->hadSingleArgument()
            ? TreeNode::flattenedBranch($shell, $object, $children[0])
            : TreeNode::branch($shell, $object, $children);

        if (! $this->allowSuperfluousKeys && count($arguments->superfluousKeys()) > 0) {
            $node = $node->withMessage(new UnexpectedArrayKeysForClass($arguments));
        }

        return $node;
    }

    /**
     * @return array<TreeNode>
     */
    private function children(Shell $shell, ArgumentsValues $arguments, RootNodeBuilder $rootBuilder): array
    {
        $children = [];

        foreach ($arguments as $argument) {
            $name = $argument->name();
            $type = $argument->type();
            $attributes = $argument->attributes();

            $child = $shell->child($name, $type, $attributes);

            if ($arguments->hasValue($name)) {
                $child = $child->withValue($arguments->getValue($name));
            }

            $children[] = $rootBuilder->build($child);
        }

        return $children;
    }

    /**
     * @param TreeNode[] $children
     */
    private function buildObject(ObjectBuilder $builder, array $children): ?object
    {
        $arguments = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return null;
            }

            $arguments[$child->name()] = $child->value();
        }

        return $builder->build($arguments);
    }
}
