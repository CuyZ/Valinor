<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Mapper\Tree\Visitor\ShellVisitor;

final class FakeShellVisitor implements ShellVisitor
{
    /** @var null|callable(Shell): Shell */
    private $callback;

    /**
     * @param null|callable(Shell): Shell $callback
     */
    public function __construct(callable $callback = null)
    {
        if ($callback) {
            $this->callback = $callback;
        }
    }

    public function visit(Shell $shell): Shell
    {
        if (! isset($this->callback)) {
            return $shell;
        }

        return ($this->callback)($shell);
    }
}
