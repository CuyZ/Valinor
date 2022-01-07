<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class ErrorCatcherNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    public function __construct(NodeBuilder $delegate)
    {
        $this->delegate = $delegate;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        try {
            return $this->delegate->build($shell, $rootBuilder);
        } catch (Message $exception) {
            return Node::error($shell, $exception);
        }
    }
}
