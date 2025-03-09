<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
final class NullFormatter implements TypeFormatter
{
    public function formatValueNode(ComplianceNode $valueNode): Node
    {
        return Node::value(null);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
