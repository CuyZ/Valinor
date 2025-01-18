<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function implode;

/** @internal */
final class ClosureNode extends Node
{
    /** @var list<string> */
    private array $use = [];

    /** @var array<ParameterDeclarationNode> */
    private array $parameters = [];

    /** @var array<Node> */
    private array $nodes = [];

    /**
     * @param non-empty-string $name
     */
    public function uses(string $name): self
    {
        $self = clone $this;
        $self->use[] = '$' . $name;

        return $self;
    }

    public function witParameters(ParameterDeclarationNode ...$parameters): self
    {
        $self = clone $this;
        $self->parameters = array_merge($self->parameters, $parameters);

        return $self;
    }

    public function withBody(Node ...$nodes): self
    {
        $self = clone $this;
        $self->nodes = [...$self->nodes, ...$nodes];

        return $self;
    }

    public function compile(Compiler $compiler): Compiler
    {
        $use = $this->use !== [] ? ' use (' . implode(', ', $this->use) . ')' : '';

        $parameters = implode(', ', array_map(
            fn (ParameterDeclarationNode $parameter) => $compiler->sub()->compile($parameter)->code(),
            $this->parameters,
        ));

        $body = $compiler->sub()->indent()->compile(...$this->nodes)->code();

        $code = <<<PHP
        function ($parameters)$use {
        $body
        }
        PHP;

        return $compiler->write($code);
    }
}
