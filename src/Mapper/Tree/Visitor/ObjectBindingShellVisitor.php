<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class ObjectBindingShellVisitor implements ShellVisitor
{
    /** @var array<string, callable(mixed): object> */
    private array $callbacks;

    /**
     * @param array<string, callable(mixed): object> $callbacks
     */
    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
    }

    public function visit(Shell $shell): Shell
    {
        $value = $shell->value();
        $signature = (string)$shell->type();

        if (! isset($this->callbacks[$signature])) {
            return $shell;
        }

        $value = $this->callbacks[$signature]($value);

        return $shell->withValue($value);
    }
}
