<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class ForEachNode extends Node
{
    public function __construct(
        private Node $value,
        private string $key,
        private string $item,
        /** @var Node|non-empty-list<Node> */
        private Node|array $body,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $body = $this->body instanceof Node ? [$this->body] : $this->body;

        $value = $compiler->sub()->compile($this->value)->code();
        $body = $compiler->sub()->indent()->compile(...$body)->code();

        $item = "\${$this->key} => \${$this->item}";

        return $compiler->write(
            <<<PHP
            foreach ($value as $item) {
            $body
            }
            PHP
        );
    }
}
