<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Node;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class RegisteredTransformersNode extends Node
{
    public function __construct(private TransformerDefinition $definition) {}

    public function compile(Compiler $compiler): Compiler
    {
        if ($this->definition->transformerTypes === [] && $this->definition->transformerAttributes === []) {
            $nodes = [
                $this->definition->typeTransformer->valueTransformationNode(Node::variable('value'))
            ];
        } else {
            $nodes = [
                Node::variable('next')->assign(
                    Node::shortClosure(
                        $this->definition->typeTransformer->valueTransformationNode(Node::variable('value'))
                    )
                )->asExpression(),

                ...array_map(
                    fn (int $key, Type $transformerType) => Node::if(
                        condition: new TypeAcceptNode($transformerType),
                        body: Node::variable('next')->assign(
                            Node::shortClosure(
                                return: Node::this()
                                    ->access('transformers')
                                    ->key(Node::value($key))
                                    ->call(arguments: [
                                        Node::variable('value'),
                                        Node::variable('next'),
                                    ]),
                            ),
                        )->asExpression(),
                    ),
                    array_keys($this->definition->transformerTypes),
                    $this->definition->transformerTypes,
                ),

                ...array_map(
                    fn (AttributeDefinition $attribute) => Node::variable('next')->assign(
                        Node::shortClosure(
                            return: (new NewAttributeNode($attribute))
                                ->wrap()
                                ->callMethod(
                                    method: 'normalize',
                                    arguments: [
                                        Node::variable('value'),
                                        Node::variable('next'),
                                    ],
                                ),
                        ),
                    )->asExpression(),
                    $this->definition->transformerAttributes,
                ),

                Node::return(
                    Node::variable('next')->call(),
                ),
            ];
        }

        return $compiler->compile(...$nodes);
    }
}
