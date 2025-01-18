<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\StdClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

/** @internal */
final class StdClassToArrayFormatter implements TypeFormatter
{
    public function __construct(
        private StdClassDefinitionNode $stdClass,
    ) {}

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $this->stdClass->mixedDefinition->typeFormatter()->manipulateTransformerClass($class);
    }

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::functionCall(
            name: 'array_map',
            arguments: [
                Node::shortClosure(
                    return: $this->stdClass->mixedDefinition->typeFormatter()->formatValueNode(Node::variable('value')),
                )->witParameters(Node::parameterDeclaration('value', 'mixed')),
                $valueNode->castToArray(),
            ],
        );
    }
}
