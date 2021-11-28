<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\NodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

final class FakeNodeBuilder implements NodeBuilder
{
    /** @var null|callable(Shell): Node */
    private $callback;

    /**
     * @param null|callable(Shell): Node $callback
     */
    public function __construct(callable $callback = null)
    {
        if ($callback) {
            $this->callback = $callback;
        }
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        if (isset($this->callback)) {
            return ($this->callback)($shell);
        }

        return Node::leaf($shell, $shell->value());
    }
}
