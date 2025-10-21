<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use WeakMap;

use function hash;

/** @internal */
final class ShapedArrayFormatter implements TypeFormatter
{
    public function __construct(
        private ShapedArrayType $type,
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

        if ($this->type->isUnsealed && $this->type->unsealedType() instanceof ArrayType) {
            $defaultDefinition = $definitionBuilder->for($this->type->unsealedType()->subType());
        } else {
            $defaultDefinition = $definitionBuilder->for(MixedType::get());
        }

        $class = $defaultDefinition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

        $elementsDefinitions = [];

        foreach ($this->type->elements as $key => $element) {
            $elementDefinition = $definitionBuilder->for($element->type());

            $class = $elementDefinition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

            $elementsDefinitions[$key] = $elementDefinition;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'array'),
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
                            (function () use ($defaultDefinition, $elementsDefinitions) {
                                $match = Node::match(Node::variable('key'));

                                foreach ($elementsDefinitions as $name => $definition) {
                                    $match = $match->withCase(
                                        condition: Node::value($name),
                                        body: $definition->typeFormatter()->formatValueNode(Node::variable('item')),
                                    );
                                }

                                return $match->withDefaultCase(
                                    $defaultDefinition->typeFormatter()->formatValueNode(Node::variable('item')),
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
        return 'transform_shaped_array_' . hash('crc32', $this->type->toString());
    }
}
