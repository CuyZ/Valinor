<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class PropertyDeclarationNode extends Node
{
    public function __construct(
        /** @var non-empty-string */
        private string $name,
        private string $type,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler->write('private ' . $this->type . ' $' . $this->name . ';');
    }
}
