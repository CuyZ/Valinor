<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler\Array;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\IterableDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class IterableToArrayNode implements TypeTransformer
{
    public function __construct(
        private IterableDefinitionNode $iterable,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod($this->methodName(), [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->iterable->subDefinition->typeTransformer->manipulateTransformerClass($class);

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
                                        return: $this->iterable->subDefinition->typeTransformer->valueTransformationNode(Node::variable('item')),
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
                                    key: 'key',
                                    item: 'item',
                                    body: Node::yield(
                                        key: Node::variable('key'),
                                        value: $this->iterable->subDefinition->typeTransformer->valueTransformationNode(Node::variable('item')),
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
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->iterable->subDefinition->type->toString()));

        return "transform_iterable_{$slug}_" . hash('xxh128', $this->iterable->subDefinition->type->toString());
    }
}
