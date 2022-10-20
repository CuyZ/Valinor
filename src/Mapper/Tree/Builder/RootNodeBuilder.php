<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class RootNodeBuilder
{
    public function __construct(private NodeBuilder $root)
    {
    }

    public function build(Shell $shell): TreeNode
    {
        return $this->root->build($shell, $this);
    }
}
