<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler\Array;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ShapedArrayDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class ShapedArrayToArrayNode implements TypeTransformer
{
    public function __construct(
        private ShapedArrayDefinitionNode $shapedArray,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod($this->methodName(), [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->shapedArray->defaultTransformer->typeTransformer->manipulateTransformerClass($class);

        foreach ($this->shapedArray->elementsDefinitions as $definition) {
            $class = $definition->typeTransformer->manipulateTransformerClass($class);
        }

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'array'),
                )
                ->withReturnType('array')
                ->withBody(
                    Node::variable('result')->assign(Node::array())->asExpression(),
                    Node::forEach(
                        Node::variable('value'),
                        'item',
                        Node::variable('result')->key(Node::variable('key'))->assign(
                            (function () {
                                $match = Node::match(Node::variable('key'));

                                foreach ($this->shapedArray->elementsDefinitions as $name => $definition) {
                                    $match = $match->withCase(
                                        Node::value($name),
                                        $definition->typeTransformer->valueTransformationNode(Node::variable('value')->key(Node::value($name))),
                                    );
                                }

                                // @todo handle unsealed array
                                return $match->withDefaultCase(
                                    $this->shapedArray->defaultTransformer->typeTransformer->valueTransformationNode(Node::variable('value')->key(Node::value($name))),
                                );
                            })(),
                        )->asExpression(),
                        'key',
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
        return 'transform_shaped_array_' . sha1($this->shapedArray->type->toString());
    }
}
