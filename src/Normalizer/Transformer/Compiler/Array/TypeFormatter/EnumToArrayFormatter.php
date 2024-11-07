<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use BackedEnum;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\EnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

final class EnumToArrayFormatter implements TypeFormatter
{
    public function __construct(
        private EnumDefinitionNode $enum,
    ) {}

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return is_a($this->enum->type->className(), BackedEnum::class, true)
            ? $valueNode->access('value')
            : $valueNode->access('name');
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
