<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;

/** @internal */
final class IterableTransformerNode implements TypeTransformer
{
    public function __construct(
        private TransformerDefinition $subDefinition,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod($this->methodName(), [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->subDefinition->manipulateTransformerClass($class);

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'iterable'),
                )
                ->withReturnType('iterable')
                ->withBody(
                    Node::if(
                        condition: Node::functionCall('is_array', [Node::variable('value')]),
                        body: Node::return(
                            Node::functionCall(
                                name: 'array_map',
                                arguments: [
                                    Node::shortClosure(
                                        return: $this->subDefinition->valueTransformationNode(Node::variable('item')),
                                    )->witParameters(Node::parameterDeclaration('item', 'mixed')),
                                    Node::variable('value'),
                                ],
                            ),
                        ),
                    ),
                    Node::return(
                        Node::anonymousFunction()
                            ->withBody(
                                Node::forEach(
                                    value: Node::variable('value'),
                                    item: 'item',
                                    key: 'key',
                                    body: Node::yield(
                                        key: Node::variable('key'),
                                        value: $this->subDefinition->valueTransformationNode(Node::variable('item')),
                                    )->asExpression(),
                                ),
                            )
                            ->witParameters(Node::parameterDeclaration('value', 'mixed'))
                            ->wrap()
                            ->call([Node::variable('value')]),
                    ),
                ),
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->subDefinition->type->toString()));

        return "transform_iterable_{$slug}_" . sha1($this->subDefinition->type->toString());
    }
}
