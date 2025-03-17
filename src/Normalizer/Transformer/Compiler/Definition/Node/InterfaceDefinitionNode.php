<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Type\Types\InterfaceType;

/** @internal */
final class InterfaceDefinitionNode implements DefinitionNode
{
    public function __construct(
        public readonly InterfaceType $type,
    ) {}
}
