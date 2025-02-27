<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\StdClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\EmptyObject;
use stdClass;

/** @internal */
final class StdClassFormatter implements TypeFormatter
{
    public function __construct(
        private StdClassDefinitionNode $stdClass,
    ) {}

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->stdClass->mixedDefinition->typeFormatter()->manipulateTransformerClass($class);

        if ($class->hasMethod('transform_stdclass')) {
            return $class;
        }

        return $class->withMethods(
            Node::method('transform_stdclass')
                ->witParameters(
                    Node::parameterDeclaration('value', stdClass::class),
                    Node::parameterDeclaration('references', \WeakMap::class),
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
                                    return: $this->stdClass->mixedDefinition->typeFormatter()->formatValueNode(Node::variable('value')),
                                )->witParameters(Node::parameterDeclaration('value', 'mixed')),
                                Node::variable('value')->castToArray(),
                            ],
                        )
                    ),
                ),
        );
    }

    public function formatValueNode(CompliantNode $valueNode): Node
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
