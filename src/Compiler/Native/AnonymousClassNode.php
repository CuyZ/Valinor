<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;
use function array_merge;
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
        $self->arguments = array_merge($self->arguments, $arguments);

        return $self;
    }

    /**
     * @param interface-string ...$interfaces
     */
    public function implements(string ...$interfaces): self
    {
        $self = clone $this;
        $self->interfaces = array_merge($self->interfaces, $interfaces);

        return $self;
    }

    public function withProperties(PropertyDeclarationNode ...$properties): self
    {
        $self = clone $this;
        $self->properties = array_merge($self->properties, $properties);

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
        $arguments = $this->arguments !== [] ?
            implode(', ', array_map(
                fn (Node $argument) => $compiler->sub()->compile($argument)->code(),
                $this->arguments,
            )) : '';

        $implements = $this->interfaces !== [] ?
            ' implements ' . implode(', ', array_map(
                fn (string $interface) => '\\' . $interface,
                $this->interfaces,
            )) : '';

        $properties = implode(PHP_EOL . PHP_EOL, array_map(
            fn (PropertyDeclarationNode $property) => $compiler->sub()->indent()->compile($property)->code(),
            $this->properties,
        ));

        $methods = implode(PHP_EOL . PHP_EOL, array_map(
            fn (MethodNode $method) => $compiler->sub()->indent()->compile($method)->code(),
            $this->methods,
        ));

        return $compiler->write(
            <<<PHP
            new class ($arguments)$implements {
            $properties
                
            $methods
            }
            PHP,
        );
    }
}