<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;

/** @internal */
final class StdClassDefinitionNode implements DefinitionNode
{
    public function __construct(
        public readonly TransformerDefinition $mixedDefinition,
    ) {}
}
