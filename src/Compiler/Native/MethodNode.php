<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;

/** @internal */
final class MethodNode extends Node
{
    /** @var 'public'|'private' */
    private string $visibility = 'private';

    /** @var non-empty-string */
    private string $name;

    private string $returnType;

    /** @var array<ParameterDeclarationNode> */
    private array $parameters = [];

    /** @var array<Node> */
    private array $nodes = [];

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function constructor(): self
    {
        return new self('__construct');
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function witParameters(ParameterDeclarationNode ...$parameters): self
    {
        $self = clone $this;
        $self->parameters = array_merge($self->parameters, $parameters);

        return $self;
    }

    /**
     * @param 'public'|'private' $visibility
     */
    public function withVisibility(string $visibility): self
    {
        $self = clone $this;
        $self->visibility = $visibility;

        return $self;
    }

    /**
     * @param non-empty-string $type
     */
    public function withReturnType(string $type): self
    {
        $self = clone $this;
        $self->returnType = $type;

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
        $visibility = $this->visibility;
        $name = $this->name;

        $parameters = implode(', ', array_map(
            fn (ParameterDeclarationNode $parameter) => $compiler->sub()->compile($parameter)->code(),
            $this->parameters,
        ));

        if ($this->name === '__construct') {
            $returnType = '';
        } else {
            $returnType = isset($this->returnType) ? ': ' . $this->returnType : ': void';
        }

        $body = $compiler->sub()->indent()->compile(...$this->nodes)->code();

        return $compiler->write(
            <<<PHP
            $visibility function $name($parameters)$returnType
            {
            $body
            }
            PHP,
        );
    }
}
