<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class ElseNode extends Node
{
    public function __construct(
        private IfNode $if,
        private Node $body,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $if = $compiler->sub()->compile($this->if)->code();
        $body = $compiler->sub()->indent()->compile($this->body)->code();

        return $compiler->write(
            <<<PHP
            $if else {
            $body
            }
            PHP,
        );
    }
}
