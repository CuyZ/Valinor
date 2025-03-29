<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;
use function implode;

/** @internal */
final class AnonymousClassNode extends Node
{
    /** @var array<Node> */
    private array $arguments = [];

    /** @var array<interface-string> */
    private array $interfaces = [];

    /** @var array<PropertyDeclarationNode> */
    private array $properties = [];

    /** @var array<non-empty-string, MethodNode> */
    private array $methods = [];

    public function withArguments(Node ...$arguments): self
    {
        $self = clone $this;
        $self->arguments = $arguments;

        return $self;
    }

    /**
     * @param interface-string ...$interfaces
     */
    public function implements(string ...$interfaces): self
    {
        $self = clone $this;
        $self->interfaces = $interfaces;

        return $self;
    }

    public function withProperties(PropertyDeclarationNode ...$properties): self
    {
        $self = clone $this;
        $self->properties = $properties;

        return $self;
    }

    public function withMethods(MethodNode ...$methods): self
    {
        $self = clone $this;

        foreach ($methods as $method) {
            $self->methods[$method->name()] = $method;
        }

        return $self;
    }

    public function hasMethod(string $name): bool
    {
        return isset($this->methods[$name]);
    }

    public function compile(Compiler $compiler): Compiler
    {
        $arguments = implode(', ', array_map(
            fn (Node $argument) => $compiler->sub()->compile($argument)->code(),
            $this->arguments,
        ));

        $compiler = $compiler->write("new class ($arguments)");

        if ($this->interfaces !== []) {
            $compiler = $compiler->write(
                ' implements ' . implode(', ', $this->interfaces),
            );
        }

        $body = [
            ...array_map(
                fn (PropertyDeclarationNode $property) => $compiler->sub()->indent()->compile($property)->code(),
                $this->properties,
            ),
            ...array_map(
                fn (MethodNode $method) => $compiler->sub()->indent()->compile($method)->code(),
                $this->methods,
            ),
        ];

        $compiler = $compiler->write(' {');

        if ($body !== []) {
            $compiler = $compiler->write(PHP_EOL . implode(PHP_EOL . PHP_EOL, $body) . PHP_EOL);
        }

        return $compiler->write('}');
    }
}
