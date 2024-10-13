<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler\Json;

use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;

final class ScalarToJsonNode
{
    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::functionCall('json_encode', [
            'value' => $valueNode,
            'flags' => Node::this()->access('formatter')->access('jsonEncodingOptions'),
        ]);
    }
}
