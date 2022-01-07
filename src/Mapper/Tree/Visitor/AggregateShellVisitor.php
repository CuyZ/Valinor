<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class AggregateShellVisitor implements ShellVisitor
{
    /** @var ShellVisitor[] */
    private array $visitors;

    public function __construct(ShellVisitor ...$visitors)
    {
        $this->visitors = $visitors;
    }

    public function visit(Shell $shell): Shell
    {
        foreach ($this->visitors as $visitor) {
            $shell = $visitor->visit($shell);
        }

        return $shell;
    }
}
