<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use BackedEnum;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

final class UnitEnumToArrayFormatter implements TypeFormatter
{
    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::ternary(
            condition: Node::instanceOf($valueNode, BackedEnum::class),
            ifTrue: $valueNode->access('value'),
            ifFalse: $valueNode->access('name'),
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
