<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveObjectType;
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

use function assert;
use function count;
use function krsort;
use function reset;
use function usort;

/** @internal */
final class UnionNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell): Node
    {
        assert($shell->type instanceof UnionType);

        $structs = [];
        $scalars = [];
        $all = [];
        $errors = [];

        foreach ($shell->type->types() as $subType) {
            // @infection-ignore-all / This is a performance optimisation, so we
            // cannot easily test this behavior.
            if ($subType instanceof NullType && $shell->value() === null) {
                return $shell->node(null);
            }

            try {
                $node = $shell->withType($subType)->build();
            } catch (CannotResolveObjectType) {
                // We catch a special case where an interface type from the
                // union has no implementation. In this case, we just ignore the
                // exception and let the other types handle the value.
                continue;
            }

            if (! $node->isValid()) {
                $errors[TypeHelper::typePriority($subType)][] = $node;

                continue;
            }

            $all[] = $node;

            if ($subType instanceof InterfaceType || $subType instanceof ClassType || $subType instanceof ShapedArrayType) {
                $structs[] = $node;
            } elseif ($subType instanceof ScalarType) {
                $scalars[] = [
                    'type' => $subType,
                    'node' => $node,
                ];
            }
        }

        if ($all === []) {
            /** @var non-empty-array<int, non-empty-list<Node>> $errors */
            krsort($errors);

            if (count(reset($errors)) === 1) {
                return reset($errors)[0];
            }

            return $shell->error(new CannotResolveTypeFromUnion($shell->value()));
        }

        if (count($all) === 1) {
            return $all[0];
        }

        // If there is only one scalar and one struct, the scalar has priority.
        if (count($scalars) === 1 && count($structs) === 1) {
            return $scalars[0]['node'];
        }

        if ($structs !== []) {
            // Structs can be either an interface, a class or a shaped array.
            // We prioritize the one with the most children, as it's the most
            // specific type. If there are multiple types with the same number
            // of children, we consider it as a collision.
            $childrenCount = [];

            foreach ($structs as $node) {
                $childrenCount[$node->childrenCount()][] = $node;
            }

            krsort($childrenCount);

            $first = reset($childrenCount);

            if (count($first) === 1) {
                return $first[0];
            }
        } elseif ($scalars !== []) {
            usort(
                $scalars,
                fn (array $a, array $b): int => TypeHelper::scalarTypePriority($b['type']) <=> TypeHelper::scalarTypePriority($a['type']),
            );

            return $scalars[0]['node'];
        }

        return $shell->error(new TooManyResolvedTypesFromUnion());
    }
}
