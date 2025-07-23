<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class ArrayKeyAccessNode extends Node
{
    public function __construct(
        private Node $node,
        private Node $key,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $key = $compiler->sub()->compile($this->key)->code();

        return $compiler
            ->compile($this->node)
            ->write('[' . $key . ']');
    }
}
