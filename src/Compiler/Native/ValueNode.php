<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function var_export;

/** @internal */
final class ValueNode extends Node
{
    public function __construct(private bool|float|int|string|null $value) {}

    public function compile(Compiler $compiler): Compiler
    {
        $value = var_export($this->value, true);

        return $compiler->write($value);
    }
}
