<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Type\CompositeTraversableType;

/** @internal */
final class TraversableDefinitionNode implements DefinitionNode
{
    public function __construct(
        public readonly CompositeTraversableType $type,
        public readonly TransformerDefinition $defaultTransformer,
        public readonly TransformerDefinition $subDefinition,
    ) {}
}
