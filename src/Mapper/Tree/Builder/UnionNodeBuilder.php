<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveTypeFromUnion;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\EnumType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\UnionType;

use function count;

/** @internal */
final class UnionNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private bool $flexible;

    public function __construct(NodeBuilder $delegate, bool $flexible)
    {
        $this->delegate = $delegate;
        $this->flexible = $flexible;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        if (! $type instanceof UnionType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $narrowedType = $this->narrow($type, $shell->value());

        return $rootBuilder->build($shell->withType($narrowedType));
    }

    /**
     * @param mixed $source
     */
    private function narrow(UnionType $type, $source): Type
    {
        $subTypes = $type->types();

        if ($source !== null && count($subTypes) === 2) {
            if ($subTypes[0] instanceof NullType) {
                return $subTypes[1];
            } elseif ($subTypes[1] instanceof NullType) {
                return $subTypes[0];
            }
        }

        foreach ($subTypes as $subType) {
            if (! $subType instanceof ScalarType) {
                continue;
            }

            if (! $this->flexible && ! $subType instanceof EnumType) {
                continue;
            }

            if ($subType->canCast($source)) {
                return $subType;
            }
        }

        throw new CannotResolveTypeFromUnion($source, $type);
    }
}
