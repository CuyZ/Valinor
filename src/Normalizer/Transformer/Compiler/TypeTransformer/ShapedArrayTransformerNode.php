<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

/** @internal */
final class ShapedArrayTransformerNode implements TypeTransformer
{
    public function __construct(
        private ShapedArrayType $type,
        private TransformerDefinition $defaultTransformer,
        /** @var array<non-empty-string, TransformerDefinition> */
        private array $elementsDefinitions,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod($this->methodName(), [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->defaultTransformer->manipulateTransformerClass($class);

        foreach ($this->elementsDefinitions as $definition) {
            $class = $definition->manipulateTransformerClass($class);
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

                                foreach ($this->elementsDefinitions as $name => $definition) {
                                    $match = $match->withCase(
                                        Node::value($name),
                                        $definition->valueTransformationNode(Node::variable('value')->key(Node::value($name)))
                                    );
                                }

                                // @todo handle unsealed array
                                return $match->withDefaultCase(
                                    $this->defaultTransformer->valueTransformationNode(Node::variable('value')->key(Node::value($name)))
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
        return 'transform_shaped_array_' . sha1($this->type->toString());
    }
}
