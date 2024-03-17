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

            if ($this->typeAcceptsValue($shell->type(), $value)) {
                return TreeNode::leaf($shell, $value);
            }
        }

        return $this->delegate->build($shell, $rootBuilder);
    }

    private function typeAcceptsValue(Type $type, mixed $value): bool
    {
        if ($type instanceof UnionType) {
            foreach ($type->types() as $subType) {
                if ($this->typeAcceptsValue($subType, $value)) {
                    return true;
                }
            }

            return false;
        }

        return ! $type instanceof CompositeTraversableType
            && ! $type instanceof ShapedArrayType
            && $type->accepts($value);
    }
}
