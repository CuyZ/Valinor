<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use BackedEnum;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;

use function CuyZ\Valinor\Compiler\ternary;

/** @internal */
final class UnitEnumFormatter implements TypeFormatter
{
    public function formatValueNode(Node $valueNode): Node
    {
        return ternary(
            condition: $valueNode->instanceOf(BackedEnum::class),
            ifTrue: $valueNode->access('value'),
            ifFalse: $valueNode->access('name'),
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        return $class;
    }
}
