<?php

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
interface NodeBuilder
{
    public function build(Shell $shell): Node;
}
