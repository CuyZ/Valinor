<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class ReturnNode extends Node
{
    public function __construct(private ?Node $node = null) {}

    public function compile(Compiler $compiler): Compiler
    {
        $code = $this->node ? ' ' . $compiler->sub()->compile($this->node)->code() : '';

        return $compiler->write("return$code;");
    }

}
