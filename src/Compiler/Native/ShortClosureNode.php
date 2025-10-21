<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;
use function implode;

/** @internal */
final class ShortClosureNode extends Node
{
    private Node $returnNode;

    /** @var array<ParameterDeclarationNode> */
    private array $parameters = [];

    public function __construct(Node $returnNode)
    {
        $this->returnNode = $returnNode;
    }

    public function witParameters(ParameterDeclarationNode ...$parameters): self
    {
        $self = clone $this;
        $self->parameters = $parameters;

        return $self;
    }

    public function compile(Compiler $compiler): Compiler
    {
        $parameters = implode(', ', array_map(
            fn (ParameterDeclarationNode $parameter) => $compiler->sub()->compile($parameter)->code(),
            $this->parameters,
        ));

        $return = $compiler->sub()->compile($this->returnNode)->code();

        return $compiler->write(
            <<<PHP
            fn ($parameters) => $return
            PHP,
        );
    }
}
