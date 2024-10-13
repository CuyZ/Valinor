<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveTypeFromUnion;
use CuyZ\Valinor\Mapper\Tree\Exception\TooManyResolvedTypesFromUnion;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\TypeHelper;

use function count;
use function krsort;
use function reset;
use function usort;

/** @internal */
final class UnionNodeBuilder implements NodeBuilder
{
    public function __construct(private NodeBuilder $delegate) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        if (! $type instanceof UnionType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $structs = [];
        $scalars = [];
        $all = [];

        foreach ($type->types() as $subType) {
            // Performance optimisation: a `NullType` only accepts a `null`
            // value, in which case the `CasterProxyNodeBuilder` would have
            // handled it already. We can safely skip it here.
            //
            // @infection-ignore-all / This is a performance optimisation, so we
            // cannot easily test this behavior.
            if ($subType instanceof NullType) {
                continue;
            }

            $node = $rootBuilder->build($shell->withType($subType));

            if (! $node->isValid()) {
                continue;
            }

            $all[] = $node;

            if ($subType instanceof InterfaceType || $subType instanceof ClassType || $subType instanceof ShapedArrayType) {
                $structs[] = $node;
            } elseif ($subType instanceof ScalarType) {
                $scalars[] = $node;
            }
        }

        if ($all === []) {
            throw new CannotResolveTypeFromUnion($shell->value(), $type);
        }

        if (count($all) === 1) {
            return $all[0];
        }

        if ($structs !== []) {
            // Structs can be either an interface, a class or a shaped array.
            // We prioritize the one with the most children, as it's the most
            // specific type. If there are multiple types with the same number
            // of children, we consider it as a collision.
            $childrenCount = [];

            foreach ($structs as $node) {
                $childrenCount[count($node->children())][] = $node;
            }

            krsort($childrenCount);

            $first = reset($childrenCount);

            if (count($first) === 1) {
                return $first[0];
            }
        } elseif ($scalars !== []) {
            usort(
                $scalars,
                fn (TreeNode $a, TreeNode $b): int => TypeHelper::typePriority($b->type()) <=> TypeHelper::typePriority($a->type()),
            );

            return $scalars[0];
        }

        throw new TooManyResolvedTypesFromUnion($type);
    }
}
