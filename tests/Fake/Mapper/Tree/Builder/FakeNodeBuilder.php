<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\Node;
use CuyZ\Valinor\Mapper\Tree\Builder\NodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Shell;

final class FakeNodeBuilder implements NodeBuilder
{
    /** @var null|callable(Shell): Node */
    private $callback;

    /**
     * @param null|callable(Shell): Node $callback
     */
    public function __construct(?callable $callback = null)
    {
        if ($callback) {
            $this->callback = $callback;
        }
    }

    public function build(Shell $shell): Node
    {
        if (isset($this->callback)) {
            return ($this->callback)($shell);
        }

        return Node::new('some value from fake node builder', 1);
    }
}
