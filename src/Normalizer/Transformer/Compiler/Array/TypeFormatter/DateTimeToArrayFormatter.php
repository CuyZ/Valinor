<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

final class DateTimeToArrayFormatter implements TypeFormatter
{
    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return $valueNode->callMethod('format', [
            Node::value('Y-m-d\\TH:i:s.uP'), // RFC 3339
        ]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
