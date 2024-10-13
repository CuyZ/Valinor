<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler\Array;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class StdClassToArrayNode implements TypeTransformer
{
    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::functionCall(
            name: 'array_map',
            arguments: [
                // @todo call "mixed_transform" instead of delegate
                Node::shortClosure(
                    return: Node::this()
                        ->access('delegate')
                        ->callMethod('transform', [
                            Node::variable('value'),
                            Node::this()->access('formatter'),
                        ]),
                )->witParameters(Node::parameterDeclaration('value', 'mixed')),
                $valueNode->castToArray(),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
