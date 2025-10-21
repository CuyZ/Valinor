<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler;

use function array_shift;
use function str_repeat;
use function str_replace;

/** @internal */
final class Compiler
{
    private string $code = '';

    /** @var non-negative-int */
    private int $indentation = 0;

    public function compile(Node ...$nodes): self
    {
        $compiler = $this;

        while ($current = array_shift($nodes)) {
            $compiler = $current->compile($compiler);

            if ($nodes !== []) {
                $compiler = $compiler->write("\n");
            }
        }

        return $compiler;
    }

    public function sub(): self
    {
        return new self();
    }

    public function write(string $code): self
    {
        $self = clone $this;
        $self->code .= $code;

        return $self;
    }

    public function indent(): self
    {
        $self = clone $this;
        $self->indentation++;

        return $self;
    }

    public function code(): string
    {
        $indent = str_repeat('    ', $this->indentation);

        return  $indent . str_replace("\n", "\n" . $indent, $this->code);
    }
}
