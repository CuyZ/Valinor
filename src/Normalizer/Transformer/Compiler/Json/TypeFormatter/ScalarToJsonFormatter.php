<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Json\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

final class ScalarToJsonFormatter implements TypeFormatter
{
    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::functionCall('fwrite', [
            Node::variable('formatter')->access('resource'),
            Node::functionCall('json_encode', [
                'value' => $valueNode,
                'flags' => Node::variable('formatter')->access('jsonEncodingOptions'),
            ]),
        ])->asExpression();
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
