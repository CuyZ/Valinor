<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Mapper\Tree\Visitor\ShellVisitor;

/** @internal */
final class ShellVisitorNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    /** @var ShellVisitor[] */
    private array $visitors;

    public function __construct(NodeBuilder $delegate, ShellVisitor ...$visitors)
    {
        $this->delegate = $delegate;
        $this->visitors = $visitors;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        foreach ($this->visitors as $visitor) {
            $shell = $visitor->visit($shell);
        }

        return $this->delegate->build($shell, $rootBuilder);
    }
}
