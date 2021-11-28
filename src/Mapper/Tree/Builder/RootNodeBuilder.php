<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

final class RootNodeBuilder
{
    private NodeBuilder $root;

    public function __construct(NodeBuilder $root)
    {
        $this->root = $root;
    }

    public function build(Shell $shell): Node
    {
        return $this->root->build($shell, $this);
    }
}
