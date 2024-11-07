<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Type\ClassType;

/** @internal */
final class ClassDefinitionNode implements DefinitionNode
{
    public function __construct(
        public readonly ClassType $type,
        // @todo find better way to handle this
        /** @var array<non-empty-string, TransformerDefinition> */
        public array $propertiesDefinitions,
    ) {}
}
