<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use BackedEnum;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Types\EnumType;

use function is_a;

/** @internal */
final class EnumFormatter implements TypeFormatter
{
    public function __construct(
        private EnumType $type,
    ) {}

    public function formatValueNode(ComplianceNode $valueNode): Node
    {
        return is_a($this->type->className(), BackedEnum::class, true)
            ? $valueNode->access('value')
            : $valueNode->access('name');
    }

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        return $class;
    }
}
