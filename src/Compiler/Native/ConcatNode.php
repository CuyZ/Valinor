<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class ConcatNode extends Node
{
    /** @var array<Node> */
    private array $nodes;

    public function __construct(Node ...$nodes)
    {
        $this->nodes = $nodes;
    }

    public function compile(Compiler $compiler): Compiler
    {
        while ($current = array_shift($this->nodes)) {
            $compiler = $current->compile($compiler);

            if (count($this->nodes) > 0) {
                $compiler = $compiler->write(' . ');
            }
        }

        return $compiler;
    }
}
