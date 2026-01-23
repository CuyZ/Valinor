<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Type;
use WeakMap;

use function array_keys;
use function array_map;
use function preg_replace;
use function serialize;
use function sha1;
use function strtolower;

/** @internal */
final class RegisteredTransformersFormatter implements TypeFormatter
{
    public function __construct(
        private Type $type,
        private TypeFormatter $delegate,
        /** @var array<int, Type> */
        private array $transformerTypes,
        /** @var list<AttributeDefinition> */
        private array $transformerAttributes = [],
    ) {}

    public function formatValueNode(ComplianceNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: $this->methodName(),
            arguments: [
                $valueNode,
                Node::variable('references'),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        $class = $this->delegate->manipulateTransformerClass($class, $definitionBuilder);

        $nodes = [
            Node::variable('next')->assign(
                Node::shortClosure(
                    $this->delegate->formatValueNode(Node::variable('value')),
                ),
            )->asExpression(),

            ...array_map(
                fn (int $key, Type $transformerType) => Node::if(
                    condition: new TypeAcceptNode(Node::variable('value'), $transformerType),
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
                function (AttributeDefinition $attribute) {
                    $node = Node::variable('next')->assign(
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
                    )->asExpression();

                    $transformerType = $attribute->class->methods->get('normalize')->parameters->at(0)->type;

                    if (! $this->type->matches($transformerType)) {
                        return Node::if($transformerType->compiledAccept(Node::variable('value')), $node);
                    }

                    return $node;
                },
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
                    Node::parameterDeclaration('references', WeakMap::class),
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
