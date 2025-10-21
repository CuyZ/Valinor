<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function implode;

/** @internal */
final class MatchNode extends Node
{
    /** @var list<array{condition: Node, body: Node}> */
    private array $cases = [];

    private Node $defaultCase;

    public function __construct(private Node $value) {}

    public function withCase(Node $condition, Node $body): self
    {
        $self = clone $this;
        $self->cases[] = ['condition' => $condition, 'body' => $body];

        return $self;
    }

    public function withDefaultCase(Node $defaultCase): self
    {
        $self = clone $this;
        $self->defaultCase = $defaultCase;

        return $self;
    }

    public function compile(Compiler $compiler): Compiler
    {
        $value = $compiler->sub()->compile($this->value)->code();
        $body = [];

        foreach ($this->cases as $case) {
            $body[] = $compiler->sub()->indent()->compile($case['condition'])->code() .
                ' => ' .
                $compiler->sub()->compile($case['body'])->code() .
                ',';
        }

        if (isset($this->defaultCase)) {
            $body[] = $compiler->sub()->indent()->write('default')->code() .
                ' => ' .
                $compiler->sub()->compile($this->defaultCase)->code() .
                ',';
        }

        $body = implode("\n", $body);

        return $compiler->write(
            <<<PHP
            match ($value) {
            $body
            }
            PHP,
        );
    }
}
