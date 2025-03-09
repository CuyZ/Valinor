<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\TraversableDefinitionNode;
use WeakMap;

/** @internal */
final class TraversableFormatter implements TypeFormatter
{
    public function __construct(
        private TraversableDefinitionNode $iterable,
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

    /**
     * If the input is an array, we use `array_map` to format all sub-values
     * easily. If the input is not an array, we return a generator that will
     * yield all transformed values one at a time.
     *
     * Generated code should look like:
     *
     * ```php
     * if (is_array($value)) {
     *     return array_map(
     *         fn ($item) => $this->some_function($item),
     *         $value,
     *     );
     * }
     *
     * return (function () {
     *     foreach($value as $key => $item) {
     *         yield $key => $this->some_function($item);
     *     }
     * })();
     * ```
     */
    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->iterable->defaultTransformer->typeFormatter()->manipulateTransformerClass($class);
        $class = $this->iterable->subDefinition->typeFormatter()->manipulateTransformerClass($class);

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'iterable'),
                    Node::parameterDeclaration('references', WeakMap::class),
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
                                        return: $this->iterable->subDefinition->typeFormatter()->formatValueNode(Node::variable('item')),
                                    )->witParameters(Node::parameterDeclaration('item', 'mixed')),
                                    Node::variable('value')
                                ],
                            ),
                        )
                    ),
                    Node::return(
                        Node::closure(
                            Node::forEach(
                                value: Node::variable('value'),
                                key: 'key',
                                item: 'item',
                                body: Node::yield(
                                    key: Node::variable('key'),
                                    value: $this->iterable->subDefinition->typeFormatter()->formatValueNode(Node::variable('item')),
                                )->asExpression(),
                            )
                        )->uses('value', 'references')->wrap()->call(),
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
