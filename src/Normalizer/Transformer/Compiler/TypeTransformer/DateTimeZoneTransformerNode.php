<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class DateTimeZoneTransformerNode implements TypeTransformer
{
    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return $valueNode->callMethod('getName');
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
