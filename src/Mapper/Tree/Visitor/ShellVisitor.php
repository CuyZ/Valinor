<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
interface ShellVisitor
{
    public function visit(Shell $shell): Shell;
}
