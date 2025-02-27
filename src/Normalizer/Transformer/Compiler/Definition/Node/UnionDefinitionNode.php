<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Type\Types\UnionType;

/** @internal */
final class UnionDefinitionNode implements DefinitionNode
{
    public function __construct(
        public readonly UnionType $type,
        public readonly TransformerDefinition $defaultDefinition,
        /** @var non-empty-list<TransformerDefinition> */
        public readonly array $definitions,
    ) {}
}
