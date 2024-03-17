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
        private string $item,
        private Node $body,
        private ?string $key = null,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $value = $compiler->sub()->compile($this->value)->code();
        $body = $compiler->sub()->indent()->compile($this->body)->code();

        $item = '$' . $this->item;

        if ($this->key !== null) {
            $item = '$' . $this->key . ' => ' . $item;
        }

        return $compiler->write(
            <<<PHP
            foreach ($value as $item) {
            $body
            }
            PHP
        );
    }
}
