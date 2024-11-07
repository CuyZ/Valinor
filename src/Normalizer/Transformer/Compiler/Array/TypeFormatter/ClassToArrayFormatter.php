<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Types\EnumType;
use WeakMap;

final class ClassToArrayFormatter implements TypeFormatter
{
    public function __construct(private ClassDefinitionNode $class) {}

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
        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        // This is a placeholder method to avoid circular references coming from
        // the class properties.
        $class = $class->withMethods(Node::method($methodName));

        foreach ($this->class->propertiesDefinitions as $propertyDefinition) {
            $class = $propertyDefinition->typeFormatter->manipulateTransformerClass($class);
        }

        $className = $this->class->type->className();

        // Checking if the class is anonymous
        if (str_contains($className, '@anonymous')) {
            $className = 'object';
        }

        $nodes = $this->checkCircularObjectReference();

        if ($this->transformationIsAppliedOnAnyProperty()) {
            $nodes = [...$nodes, ...$this->arrayObjectTransformationNode()];
        } else {
            $nodes[] = Node::return($this->valuesNode(Node::variable('value')))->asExpression();
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', $className),
                    Node::parameterDeclaration('formatter', Formatter::class),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('array')
                ->withBody(...$nodes),
        );
    }

    private function checkCircularObjectReference(): array
    {
        return [
            Node::if(
                condition: Node::functionCall('isset', [Node::variable('references')->key(Node::variable('value'))]),
                body: Node::throw(
                    Node::newClass(CircularReferenceFoundDuringNormalization::class, Node::variable('value')),
                )->asExpression(),
            ),
            Node::variable('references')->assign(Node::variable('references')->clone())->asExpression(),
            Node::variable('references')->key(Node::variable('value'))->assign(Node::value(true))->asExpression(),
        ];
    }

    private function valuesNode(CompliantNode $valueNode): Node
    {
        return Node::shortClosure(
            return: Node::functionCall(
                name: 'get_object_vars',
                arguments: [Node::this()],
            ),
        )->wrap()->callMethod('call', [$valueNode]);
    }

    /**
     * @return list<Node>
     */
    private function arrayObjectTransformationNode(): array
    {
        $nodes = [];

        $valuesNode = $this->valuesNode(Node::variable('value'));

        $nodes[] = Node::variable('values')->assign($valuesNode)->asExpression();
        $nodes[] = Node::variable('transformed')->assign(Node::array())->asExpression();

        foreach ($this->class->propertiesDefinitions as $name => $property) {
            if ($property->hasKeyTransformation()) {
                $nodes[] = Node::variable('key')->assign(Node::value($name))->asExpression();

                foreach ($property->keyTransformerAttributes as $attribute) {
                    $nodes[] = Node::variable('key')->assign(
                        (new NewAttributeNode($attribute))->wrap()->callMethod(
                            method: 'normalizeKey',
                            arguments: [Node::variable('key')],
                        ),
                    )->asExpression();
                }

                $key = Node::variable('key');
            } else {
                $key = Node::value($name);
            }

            $nodes[] = Node::variable('transformed')
                ->key($key)
                ->assign($property->typeFormatter->formatValueNode(
                    Node::variable('values')->key(Node::value($name)),
                ))->asExpression();
        }

        $nodes[] = Node::return(Node::variable('transformed'));

        return $nodes;
    }

    private function transformationIsAppliedOnAnyProperty(): bool
    {
        foreach ($this->class->propertiesDefinitions as $definition) {
            if (! $definition->type instanceof ScalarType && ! $definition->type instanceof EnumType) {
                return true;
            }

            if ($definition->hasTransformation() || $definition->hasKeyTransformation()) {
                return true;
            }
        }

        return false;
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
