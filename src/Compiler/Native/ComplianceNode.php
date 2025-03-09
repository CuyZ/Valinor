<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class ComplianceNode extends Node
{
    public function __construct(private Node $node) {}

    /**
     * @param non-empty-string $value
     */
    public function access(string $value): self
    {
        return new self(new VariableAccessNode($this, $value));
    }

    /**
     * @no-named-arguments
     */
    public function and(Node ...$nodes): self
    {
        return new self(new LogicalAndNode($this, ...$nodes));
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
     * @param non-empty-string $method
     * @param array<Node> $arguments
     */
    public function callMethod(string $method, array $arguments = []): self
    {
        return new self(new MethodCallNode($this, $method, $arguments));
    }

    public function different(Node $right): self
    {
        return new self(new DifferentNode($this, $right));
    }

    public function equals(Node $right): self
    {
        return new self(new EqualsNode($this, $right));
    }

    /**
     * @no-named-arguments
     */
    public function or(Node ...$nodes): self
    {
        return new self(new LogicalOrNode($this, ...$nodes));
    }

    /**
     * @param class-string $className
     */
    public function instanceOf(string $className): self
    {
        return new self(new InstanceOfNode($this, $className));
    }

    public function isLessThan(Node $right): self
    {
        return new self(new LessThanNode($this, $right));
    }

    public function isLessOrEqualsTo(Node $right): self
    {
        return new self(new LessOrEqualsToNode($this, $right));
    }

    public function isGreaterThan(Node $right): self
    {
        return new self(new GreaterThanNode($this, $right));
    }

    public function isGreaterOrEqualsTo(Node $right): self
    {
        return new self(new GreaterOrEqualsToNode($this, $right));
    }

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler->compile($this->node);
    }
}
