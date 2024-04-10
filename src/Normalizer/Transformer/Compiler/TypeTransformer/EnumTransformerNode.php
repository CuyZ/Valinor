<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer;

use BackedEnum;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\Types\EnumType;

/** @internal */
final class EnumTransformerNode implements TypeTransformer
{
    public function __construct(private EnumType $type) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return is_a($this->type->className(), BackedEnum::class, true)
            ? $valueNode->access('value')
            : $valueNode->access('name');
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
