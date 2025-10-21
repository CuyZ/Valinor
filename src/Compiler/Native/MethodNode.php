<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;
use function implode;

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
        $self->parameters = $parameters;

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
        $self->nodes = $nodes;

        return $self;
    }

    public function compile(Compiler $compiler): Compiler
    {
        $parameters = implode(', ', array_map(
            fn (ParameterDeclarationNode $parameter) => $compiler->sub()->compile($parameter)->code(),
            $this->parameters,
        ));

        $compiler = $compiler->write("$this->visibility function $this->name($parameters)");

        if ($this->name !== '__construct') {
            $compiler = $compiler->write(': ' . ($this->returnType ?? 'void'));
        }

        if ($this->nodes === []) {
            return $compiler->write(' {}');
        }

        $body = $compiler->sub()->indent()->compile(...$this->nodes)->code();

        return $compiler->write(PHP_EOL . '{' . PHP_EOL . $body . PHP_EOL . '}');
    }
}
