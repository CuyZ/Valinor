<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

/** @internal */
final class ShapedArrayDefinitionNode implements DefinitionNode
{
    public function __construct(
        public readonly ShapedArrayType $type,
        public readonly TransformerDefinition $defaultTransformer,
        /** @var array<non-empty-string, TransformerDefinition> */
        public readonly array $elementsDefinitions,
    ) {}
}
