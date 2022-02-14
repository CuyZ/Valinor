<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal  */
final class CasterProxyNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    public function __construct(NodeBuilder $delegate)
    {
        $this->delegate = $delegate;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $value = $shell->value();

        if ($shell->type()->accepts($value)) {
            return Node::leaf($shell, $value);
        }

        return $this->delegate->build($shell, $rootBuilder);
    }
}
