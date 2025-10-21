<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Normalizer\Transformer\EmptyObject;
use CuyZ\Valinor\Type\Types\MixedType;
use stdClass;
use WeakMap;

/** @internal */
final class StdClassFormatter implements TypeFormatter
{
    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        if ($class->hasMethod('transform_stdclass')) {
            return $class;
        }

        $defaultDefinition = $definitionBuilder->for(MixedType::get());

        $class = $defaultDefinition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

        return $class->withMethods(
            Node::method('transform_stdclass')
                ->witParameters(
                    Node::parameterDeclaration('value', stdClass::class),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('mixed')
                ->withBody(
                    Node::variable('values')->assign(Node::variable('value')->castToArray())->asExpression(),
                    Node::if(
                        condition: Node::variable('values')->equals(Node::array()),
                        body: Node::return(
                            Node::class(EmptyObject::class)->callStaticMethod('get'),
                        ),
                    ),
                    Node::return(
                        Node::functionCall(
                            name: 'array_map',
                            arguments: [
                                Node::shortClosure(
                                    return: $defaultDefinition->typeFormatter()->formatValueNode(Node::variable('value')),
                                )->witParameters(Node::parameterDeclaration('value', 'mixed')),
                                Node::variable('value')->castToArray(),
                            ],
                        )
                    ),
                ),
        );
    }

    public function formatValueNode(ComplianceNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: 'transform_stdclass',
            arguments: [
                $valueNode,
                Node::variable('references'),
            ],
        );
    }
}
