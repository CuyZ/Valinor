<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Json\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\JsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\TraversableDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

final class TraversableToJsonFormatter implements TypeFormatter
{
    public function __construct(
        private TraversableDefinitionNode $iterable,
    ) {}

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: $this->methodName(),
            arguments: [$valueNode, Node::variable('formatter')],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->iterable->subDefinition->typeFormatter->manipulateTransformerClass($class);

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'iterable'),
                    Node::parameterDeclaration('formatter', JsonFormatter::class),
                )
                ->withBody(
                    Node::forEach(
                        value: Node::variable('value'),
                        key: 'key',
                        item: 'item',
                        body: [
                            Node::functionCall('fwrite', [
                                Node::variable('formatter')->access('resource'),
                                Node::concat(
                                    Node::functionCall('json_encode', [
                                        'value' => Node::variable('key'),
                                        'flags' => Node::variable('formatter')->access('jsonEncodingOptions'),
                                    ]),
                                    Node::value(':'),
                                ),
                            ])->asExpression(),
                            $this->iterable->subDefinition->typeFormatter->formatValueNode(Node::variable('item')),
                        ],
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
