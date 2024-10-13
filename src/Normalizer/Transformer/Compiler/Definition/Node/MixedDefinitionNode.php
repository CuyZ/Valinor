<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;

/** @internal */
final class MixedDefinitionNode implements DefinitionNode
{
    public function __construct(
        /** @var list<TransformerDefinition> */
        public readonly array $definitions,
    ) {}
}
