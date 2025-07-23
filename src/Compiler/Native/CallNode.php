<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;
use function implode;

/** @internal */
final class CallNode extends Node
{
    public function __construct(
        private Node $node,
        /** @var array<Node> */
        private array $arguments = [],
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $compiler = $compiler
            ->compile($this->node)
            ->write('(');

        if ($this->arguments !== []) {
            $arguments = array_map(
                fn (Node $argument) => $compiler->sub()->compile($argument)->code(),
                $this->arguments,
            );

            $compiler = $compiler->write(implode(', ', $arguments));
        }

        return $compiler->write(')');
    }
}
