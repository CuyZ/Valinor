<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node;

use CuyZ\Valinor\Type\Types\EnumType;

/** @internal */
final class EnumDefinitionNode implements DefinitionNode
{
    public function __construct(
        public readonly EnumType $type,
    ) {}
}
