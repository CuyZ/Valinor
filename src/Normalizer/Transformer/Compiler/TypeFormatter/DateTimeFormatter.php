<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;

use function CuyZ\Valinor\Compiler\value;

/** @internal */
final class DateTimeFormatter implements TypeFormatter
{
    public function formatValueNode(Node $valueNode): Node
    {
        return $valueNode->callMethod('format', [
            value('Y-m-d\\TH:i:s.uP'), // RFC 3339
        ]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        return $class;
    }
}
