<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\NodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\TreeNode;
use CuyZ\Valinor\Mapper\Tree\Shell;

final class FakeNodeBuilder implements NodeBuilder
{
    /** @var null|callable(Shell): TreeNode */
    private $callback;

    /**
     * @param null|callable(Shell): TreeNode $callback
     */
    public function __construct(?callable $callback = null)
    {
        if ($callback) {
            $this->callback = $callback;
        }
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        if (isset($this->callback)) {
            return ($this->callback)($shell);
        }

        return TreeNode::leaf($shell, $shell->value());
    }
}
