<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class IfNode extends Node
{
    public function __construct(
        private Node $condition,
        private Node $body,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $condition = $compiler->sub()->compile($this->condition)->code();
        $body = $compiler->sub()->indent()->compile($this->body)->code();

        return $compiler->write(
            <<<PHP
            if ($condition) {
            $body
            }
            PHP,
        );
    }
}
