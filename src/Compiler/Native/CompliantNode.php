<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class CompliantNode extends Node
{
    public function __construct(private Node $node) {}

    public function access(string $value): self
    {
        return new self(new VariableAccessNode($this, $value));
    }

    public function castToArray(): self
    {
        return new self(CastNode::toArray($this));
    }

    public function clone(): self
    {
        return new self(new CloneNode($this));
    }

    public function key(Node $key): self
    {
        return new self(new ArrayKeyAccessNode($this, $key));
    }

    public function assign(Node $value): self
    {
        return new self(new AssignNode($this, $value));
    }

    /**
     * @param array<Node> $arguments
     */
    public function call(array $arguments = []): self
    {
        return new self(new CallNode($this, $arguments));
    }

    /**
     * @param array<Node> $arguments
     */
    public function callMethod(string $method, array $arguments = []): self
    {
        return new self(new MethodCallNode($this, $method, $arguments));
    }

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler->compile($this->node);
    }
}
