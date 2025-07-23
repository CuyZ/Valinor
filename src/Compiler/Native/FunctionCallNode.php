<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class FunctionCallNode extends Node
{
    public function __construct(
        /** @var non-empty-string */
        private string $name,
        /** @var array<Node> */
        private array $arguments = [],
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler
            ->compile(new CallNode(new FunctionNameNode($this->name), $this->arguments));
    }
}
