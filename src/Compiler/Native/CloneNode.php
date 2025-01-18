<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class CloneNode extends Node
{
    public function __construct(
        private Node $node,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler
            ->write('clone ')
            ->compile($this->node);
    }
}
