<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class LessThanNode extends Node
{
    public function __construct(
        private Node $left,
        private Node $right,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler
            ->compile($this->left)
            ->write(' < ')
            ->compile($this->right);
    }
}
