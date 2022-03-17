<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class AttributeShellVisitor implements ShellVisitor
{
    public function visit(Shell $shell): Shell
    {
        $visitors = $shell->attributes()->ofType(ShellVisitor::class);

        foreach ($visitors as $visitor) {
            $shell = $visitor->visit($shell);
        }

        return $shell;
    }
}
