<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer;

use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Type\Type;

final class RegisteredTransformersTypeTransformer implements TypeTransformer
{
    public function __construct(
        private Type $type,
        /** @var array<int, Type> */
        private array $transformerTypes,
        /** @var AttributeDefinition */
        private array $transformerAttributes,
        private TypeTransformer $delegate,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod($this->methodName(), [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        $nodes = [
            Node::variable('next')->assign(
                Node::shortClosure(
                    $this->delegate->valueTransformationNode(Node::variable('value')),
                ),
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
                array_keys($this->transformerTypes),
                $this->transformerTypes,
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
                $this->transformerAttributes,
            ),

            Node::return(
                Node::variable('next')->call(),
            ),
        ];

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                )
                ->withReturnType('mixed')
                ->withBody(...$nodes),
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->type->toString()));

        return "transform_{$slug}_" . sha1(serialize([$this->type->toString(), $this->transformerAttributes]));
    }
}
