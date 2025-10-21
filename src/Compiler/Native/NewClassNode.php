<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;
use function implode;

/** @internal */
final class NewClassNode extends Node
{
    /** @var class-string */
    private string $className;

    /** @var array<Node> */
    private array $arguments;

    /**
     * @param class-string $className
     */
    public function __construct(string $className, Node ...$arguments)
    {
        $this->className = $className;
        $this->arguments = $arguments;
    }

    public function compile(Compiler $compiler): Compiler
    {
        $arguments = array_map(
            fn (Node $argument) => $compiler->sub()->compile($argument)->code(),
            $this->arguments,
        );
        $arguments = implode(', ', $arguments);

        return $compiler->write("new {$this->className}($arguments)");
    }
}
