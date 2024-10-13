<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler\Array;

use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Types\EnumType;

final class ClassToArrayNode implements TypeTransformer
{
    public function __construct(
        private ClassDefinitionNode $class,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        if ($this->transformationIsAppliedOnAnyProperty()) {
            return Node::this()->callMethod($this->methodName(), [$valueNode]);
        }

        return $this->valuesNode($valueNode);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $methodName = $this->methodName();

        if ($this->transformationIsAppliedOnAnyProperty() && ! $class->hasMethod($methodName)) {
            $className = $this->class->type->className();

            // Checking if the class is anonymous
            if (str_contains($className, '@')) {
                $className = 'object';
            }

            $class = $class->withMethods(
                Node::method($methodName)
                    ->witParameters(
                        Node::parameterDeclaration('value', $className),
                    )
                    ->withReturnType('array')
                    ->withBody($this->arrayObjectTransformationNode()),
            );
        }

        foreach ($this->class->propertiesDefinitions as $propertyDefinition) {
            $class = $propertyDefinition->typeTransformer->manipulateTransformerClass($class);
        }

        return $class;
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

    private function arrayObjectTransformationNode(): Node
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
                ->assign($property->typeTransformer->valueTransformationNode(
                    Node::variable('values')->key(Node::value($name)),
                ))->asExpression();
        }

        $nodes[] = Node::return(Node::variable('transformed'));

        return Node::aggregate(...$nodes);
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

        return "transform_object_{$slug}_" . sha1($this->class->type->toString());
    }
}
