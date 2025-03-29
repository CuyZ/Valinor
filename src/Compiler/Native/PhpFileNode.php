<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class PhpFileNode extends Node
{
    /** @var array<Node> */
    private array $nodes;

    public function __construct(Node ...$nodes)
    {
        $this->nodes = $nodes;
    }

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler
            ->write("<?php\n\ndeclare(strict_types=1);\n\n")
            ->compile(...$this->nodes);
    }
}
