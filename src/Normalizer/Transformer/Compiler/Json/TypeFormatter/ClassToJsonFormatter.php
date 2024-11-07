<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Json\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\JsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

final class ClassToJsonFormatter implements TypeFormatter
{
    public function __construct(private ClassDefinitionNode $class) {}

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: $this->methodName(),
            arguments: [$valueNode, Node::variable('formatter')],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        foreach ($this->class->propertiesDefinitions as $propertyDefinition) {
            $class = $propertyDefinition->typeFormatter->manipulateTransformerClass($class);
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', $this->class->type->className()),
                    Node::parameterDeclaration('formatter', JsonFormatter::class),
                )
                ->withBody(...$this->methodBody()),
        );
    }

    /**
     * @return list<Node>
     */
    private function methodBody(): array
    {
        $nodes = [];

        $nodes[] = Node::variable('values')->assign(
            Node::shortClosure(
                return: Node::functionCall(
                    name: 'get_object_vars',
                    arguments: [Node::this()],
                ),
            )->wrap()->callMethod('call', [Node::variable('value')]),
        )->asExpression();

        $nodes[] = Node::functionCall('fwrite', [
            Node::variable('formatter')->access('resource'),
            Node::value('{'),
        ])->asExpression();

        foreach ($this->class->propertiesDefinitions as $name => $propertyDefinition) {
            $nodes[] = Node::functionCall('fwrite', [
                Node::variable('formatter')->access('resource'),
                Node::concat(
                    Node::functionCall('json_encode', [
                        'value' => Node::value($name),
                        'flags' => Node::variable('formatter')->access('jsonEncodingOptions'),
                    ]),
                    Node::value(':'),
                ),
            ])->asExpression();

            $nodes[] = $propertyDefinition->typeFormatter->formatValueNode(Node::variable('values')->key(Node::value($name)));

            if ($name !== array_key_last($this->class->propertiesDefinitions)) {
                $nodes[] = Node::functionCall('fwrite', [
                    Node::variable('formatter')->access('resource'),
                    Node::value(','),
                ])->asExpression();
            }
        }

        $nodes[] = Node::functionCall('fwrite', [
            Node::variable('formatter')->access('resource'),
            Node::value('}'),
        ])->asExpression();

        return $nodes;
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->class->type->toString()));

        return "transform_object_{$slug}_" . hash('xxh128', $this->class->type->toString());
    }
}
