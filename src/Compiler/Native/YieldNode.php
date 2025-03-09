<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class YieldNode extends Node
{
    public function __construct(
        private Node $key,
        private Node $value,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler
            ->write('yield ')
            ->compile($this->key)
            ->write(' => ')
            ->compile($this->value);
    }
}
