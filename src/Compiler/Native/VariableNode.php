<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class VariableNode extends Node
{
    public function __construct(private string $name) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler->write('$' . $this->name);
    }
}
