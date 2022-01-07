<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class VisitorNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    /** @var array<callable(Node): void> */
    private array $callbacks;

    /**
     * @param array<callable(Node): void> $callbacks
     */
    public function __construct(NodeBuilder $delegate, array $callbacks)
    {
        $this->delegate = $delegate;
        $this->callbacks = $callbacks;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $node = $this->delegate->build($shell, $rootBuilder);

        foreach ($this->callbacks as $callback) {
            $callback($node);
        }

        return $node;
    }
}
