<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ShapedArrayDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use WeakMap;

final class ShapedArrayToArrayFormatter implements TypeFormatter
{
    public function __construct(
        private ShapedArrayDefinitionNode $shapedArray,
    ) {}

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: $this->methodName(),
            arguments: [
                $valueNode,
                Node::variable('formatter'),
                Node::variable('references'),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->shapedArray->defaultTransformer->typeFormatter->manipulateTransformerClass($class);

        foreach ($this->shapedArray->elementsDefinitions as $definition) {
            $class = $definition->typeFormatter->manipulateTransformerClass($class);
        }

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'array'),
                    Node::parameterDeclaration('formatter', Formatter::class),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('array')
                ->withBody(
                    Node::variable('result')->assign(Node::array())->asExpression(),
                    Node::forEach(
                        value: Node::variable('value'),
                        key: 'key',
                        item: 'item',
                        body: Node::variable('result')->key(Node::variable('key'))->assign(
                            (function () {
                                $match = Node::match(Node::variable('key'));

                                foreach ($this->shapedArray->elementsDefinitions as $name => $definition) {
                                    $match = $match->withCase(
                                        Node::value($name),
                                        $definition->typeFormatter->formatValueNode(Node::variable('value')->key(Node::value($name))),
                                    );
                                }

                                // @todo handle unsealed array
                                return $match->withDefaultCase(
                                    $this->shapedArray->defaultTransformer->typeFormatter->formatValueNode(Node::variable('value')->key(Node::value($name))),
                                );
                            })(),
                        )->asExpression(),
                    ),
                    Node::return(Node::variable('result')),
                ),
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        return 'transform_shaped_array_' . hash('xxh128', $this->shapedArray->type->toString());
    }
}
