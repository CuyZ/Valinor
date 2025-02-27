<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function current;
use function is_array;
use function key;
use function var_export;

/** @internal */
final class ValueNode extends Node
{
    public function __construct(
        /** @var array<mixed>|bool|float|int|string|null */
        private array|bool|float|int|string|null $value,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $this->compileValue($this->value, $compiler);
    }

    private function compileValue(mixed $value, Compiler $compiler): Compiler
    {
        if (is_array($value)) {
            $compiler = $compiler->write('[');

            while (($val = current($value)) !== false) {
                $compiler = $compiler->write(var_export(key($value), true) . ' => ');
                $compiler = $this->compileValue($val, $compiler);
                next($value);

                if (current($value)) {
                    $compiler = $compiler->write(', ');
                }
            }

            $compiler = $compiler->write(']');
        } else {
            $compiler = $compiler->write(var_export($value, true));
        }

        return $compiler;
    }
}
