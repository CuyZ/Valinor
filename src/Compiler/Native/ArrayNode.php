<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function implode;
use function var_export;

/** @internal */
final class ArrayNode extends Node
{
    public function __construct(
        /** @var array<Node> */
        private array $assignments
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        if ($this->assignments === []) {
            return $compiler->write('[]');
        }

        $sub = [];

        foreach ($this->assignments as $key => $assignment) {
            $sub[] = '    ' . var_export($key, true) . ' => ' . $compiler->sub()->compile($assignment)->code() . ",";
        }

        $sub = implode("\n", $sub);

        return $compiler->write("[\n$sub\n]");
    }
}
