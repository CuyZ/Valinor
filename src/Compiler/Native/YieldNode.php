<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class YieldNode extends Node
{
    public function __construct(
        private Node $value,
        private ?Node $key = null,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $compiler = $compiler->write('yield ');

        if ($this->key) {
            $compiler = $compiler->compile($this->key)->write(' => ');
        }

        return $compiler->compile($this->value);
    }
}
