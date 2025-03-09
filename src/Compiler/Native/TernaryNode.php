<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class TernaryNode extends Node
{
    public function __construct(
        private Node $condition,
        private Node $ifTrue,
        private Node $ifFalse,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler
            ->compile($this->condition)
            ->write(' ? ')
            ->compile($this->ifTrue)
            ->write(' : ')
            ->compile($this->ifFalse);
    }
}
