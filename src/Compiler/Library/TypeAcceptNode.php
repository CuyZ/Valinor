<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Library;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class TypeAcceptNode extends Node
{
    public function __construct(
        private ComplianceNode $node,
        private Type $type,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        return $compiler->compile($this->type->compiledAccept($this->node));
    }
}
