<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Resolver\Union\UnionNarrower;
use CuyZ\Valinor\Type\Types\UnionType;

/** @internal */
final class UnionNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private UnionNarrower $unionNarrower;

    public function __construct(NodeBuilder $delegate, UnionNarrower $unionNarrower)
    {
        $this->delegate = $delegate;
        $this->unionNarrower = $unionNarrower;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        if (! $type instanceof UnionType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $narrowedType = $this->unionNarrower->narrow($type, $value);

        return $rootBuilder->build($shell->withType($narrowedType));
    }
}
