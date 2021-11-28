<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\CompositeTraversableType;

use CuyZ\Valinor\Type\Types\ListType;

use CuyZ\Valinor\Type\Types\NonEmptyListType;

use function assert;
use function is_iterable;

final class ListNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ListType || $type instanceof NonEmptyListType);

        if (null === $value) {
            return Node::branch($shell, [], []);
        }

        if (! is_iterable($value)) {
            throw new SourceMustBeIterable($value, $type);
        }

        $children = $this->children($type, $shell, $rootBuilder);
        $array = $this->buildArray($children);

        return Node::branch($shell, $array, $children);
    }

    /**
     * @return array<Node>
     */
    private function children(CompositeTraversableType $type, Shell $shell, RootNodeBuilder $rootBuilder): array
    {
        /** @var iterable<mixed> $values */
        $values = $shell->value();
        $subType = $type->subType();

        $key = 0;
        $children = [];

        foreach ($values as $value) {
            $child = $shell->child((string)$key, $subType, $value);
            $children[$key] = $rootBuilder->build($child);

            $key++;
        }

        return $children;
    }

    /**
     * @param array<Node> $children
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
