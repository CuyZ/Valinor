<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class CastNode extends Node
{
    private function __construct(
        private string $type,
        private Node $node,
    ) {}

    public static function toArray(Node $node): self
    {
        return new self('array', $node);
    }

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler
            ->write('(' . $this->type . ')')
            ->compile($this->node);
    }
}
