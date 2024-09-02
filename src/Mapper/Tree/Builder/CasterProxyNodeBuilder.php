<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnionType;

/** @internal */
final class CasterProxyNodeBuilder implements NodeBuilder
{
    public function __construct(private NodeBuilder $delegate) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        if ($shell->hasValue()) {
            $value = $shell->value();

            $typeAcceptingValue = $this->typeAcceptingValue($shell->type(), $value);

            if ($typeAcceptingValue) {
                return TreeNode::leaf($shell->withType($typeAcceptingValue), $value);
            }
        }

        return $this->delegate->build($shell, $rootBuilder);
    }

    private function typeAcceptingValue(Type $type, mixed $value): ?Type
    {
        if ($type instanceof UnionType) {
            foreach ($type->types() as $subType) {
                if ($this->typeAcceptingValue($subType, $value)) {
                    return $subType;
                }
            }

            return null;
        }

        if ($type instanceof CompositeTraversableType || $type instanceof ShapedArrayType) {
            return null;
        }

        if ($type->accepts($value)) {
            return $type;
        }

        return null;
    }
}
