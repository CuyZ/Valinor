<?php

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

interface NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node;
}
