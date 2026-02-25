<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Normalizer\Transformer\TransformerContainer;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use WeakMap;

use function hash;
use function preg_replace;
use function str_contains;
use function strtolower;

/** @internal */
final class ClassFormatter implements TypeFormatter
{
    public function __construct(
        private ClassDefinition $class,
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

        // This is a placeholder method to avoid circular references coming from
        // the class properties.
        $class = $class->withMethods(Node::method($methodName));

        $valuesNode = $this->valuesNode(Node::variable('value'));

        $nodes = [
            ...$this->checkCircularObjectReference(),
            Node::variable('values')->assign($valuesNode)->asExpression(),
        ];

        $transformedNodes = [
            Node::variable('transformed')->assign(Node::array())->asExpression(),
        ];

        $shouldUseTransformedNodes = false;

        foreach ($this->class->properties as $property) {
            $propertyType = $property->type instanceof UnresolvableType ? $property->nativeType : $property->type;

            $propertyDefinition = $definitionBuilder->for($propertyType);

            if (! $property->nativeType instanceof MixedType) {
                $propertyDefinition = $propertyDefinition->markAsSure();
            }

            $propertyDefinition = $propertyDefinition->withTransformerAttributes(
                $property->attributes
                    ->filter(TransformerContainer::filterTransformerAttributes(...))
                    ->filter(static function (AttributeDefinition $attribute) use ($propertyType): bool {
                        $transformerType = $attribute->class->methods->get('normalize')->parameters->at(0)->type;

                        // We filter out transformer attributes that don't
                        // match the property type because they will never
                        // be called anyway.
                        return $transformerType->matches($propertyType)
                            || $propertyType->matches($transformerType);
                    })
                    ->toArray(),
            );

            $typeFormatter = $propertyDefinition->typeFormatter();

            $keyTransformerAttributes = $property->attributes
                ->filter(TransformerContainer::filterKeyTransformerAttributes(...))
                ->toArray();

            if ($keyTransformerAttributes === []) {
                $key = Node::value($property->name);
            } else {
                $transformedNodes[] = Node::variable('key')->assign(Node::value($property->name))->asExpression();

                foreach ($keyTransformerAttributes as $attribute) {
                    $transformedNodes[] = Node::variable('key')->assign(
                        (new NewAttributeNode($attribute))->wrap()->callMethod(
                            method: 'normalizeKey',
                            arguments: [Node::variable('key')],
                        ),
                    )->asExpression();
                }

                $key = Node::variable('key');
            }

            $transformedNodes[] = Node::variable('transformed')
                ->key($key)
                ->assign($typeFormatter->formatValueNode(
                    Node::variable('values')->key(Node::value($property->name)),
                ))->asExpression();

            $class = $typeFormatter->manipulateTransformerClass($class, $definitionBuilder);

            $shouldUseTransformedNodes = $shouldUseTransformedNodes
                || $propertyDefinition->hasTransformers()
                || $keyTransformerAttributes !== []
                || ! $property->nativeType instanceof ScalarType;
        }

        if ($shouldUseTransformedNodes) {
            $nodes = [
                ...$nodes,
                ...$transformedNodes,
                Node::return(Node::variable('transformed'))
            ];
        } else {
            $nodes[] = Node::return(Node::variable('values'));
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', str_contains($this->class->name, '@anonymous') ? 'object' : $this->class->name),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('array')
                ->withBody(...$nodes),
        );
    }

    /**
     * @return list<Node>
     */
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
            Node::variable('references')->key(Node::variable('value'))->assign(Node::variable('value'))->asExpression(),
        ];
    }

    /**
     * This method returns a node responsible for getting the properties' values
     * of an object.
     *
     * First scenario: if all properties are public, we can extract them easily
     * by accessing the properties directly, resulting in a code like this:
     *
     * ```
     * [
     *     'someProperty' => $value->someProperty,
     *     'anotherProperty' => $value->anotherProperty,
     * ]
     * ```
     *
     * Second scenario: at least one property is protected/private. In this
     * case, we need to "extract" the properties using `get_object_vars`:
     *
     * ```
     * (fn () => get_object_vars($this))->call($value)
     * ```
     */
    private function valuesNode(ComplianceNode $valueNode): Node
    {
        $allPropertiesArePublic = true;
        $assignments = [];

        foreach ($this->class->properties as $property) {
            $assignments[$property->name] = $valueNode->access($property->name);

            $allPropertiesArePublic = $allPropertiesArePublic && $property->isPublic;
        }

        if ($allPropertiesArePublic) {
            return Node::array($assignments);
        }

        return Node::shortClosure(
            return: Node::functionCall(
                name: 'get_object_vars',
                arguments: [Node::this()],
            ),
        )->wrap()->callMethod('call', [$valueNode]);
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->class->type->toString()));

        return "transform_object_{$slug}_" . hash('crc32', $this->class->type->toString());
    }
}
