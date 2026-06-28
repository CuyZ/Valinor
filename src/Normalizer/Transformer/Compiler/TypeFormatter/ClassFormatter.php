<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
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

use function CuyZ\Valinor\Compiler\{array_, call, clone_, if_, newClass, param, return_, shortClosure, this, throw_, value, variable};
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

    public function formatValueNode(Node $valueNode): Node
    {
        return this()->callMethod(
            method: $this->methodName(),
            arguments: [
                $valueNode,
                variable('references'),
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
        $class = $class->withMethod($methodName);

        $valuesNode = $this->valuesNode(variable('value'));

        $nodes = [
            ...$this->checkCircularObjectReference(),
            variable('values')->assign($valuesNode)->asStatement(),
        ];

        $transformedNodes = [
            variable('transformed')->assign(value([]))->asStatement(),
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
                $key = value($property->name);
            } else {
                $transformedNodes[] = variable('key')->assign(value($property->name))->asStatement();

                foreach ($keyTransformerAttributes as $attribute) {
                    $transformedNodes[] = variable('key')->assign(
                        (new NewAttributeNode($attribute))->wrap()->callMethod(
                            method: 'normalizeKey',
                            arguments: [variable('key')],
                        ),
                    )->asStatement();
                }

                $key = variable('key');
            }

            $transformedNodes[] = variable('transformed')
                ->key($key)
                ->assign($typeFormatter->formatValueNode(
                    variable('values')->key(value($property->name)),
                ))->asStatement();

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
                return_(variable('transformed'))
            ];
        } else {
            $nodes[] = return_(variable('values'));
        }

        return $class->withMethod(
            name: $methodName,
            parameters: [
                param('value', str_contains($this->class->name, '@anonymous') ? 'object' : $this->class->name),
                param('references', WeakMap::class),
            ],
            returnType: 'array',
            body: $nodes,
        );
    }

    /**
     * @return list<Node>
     */
    private function checkCircularObjectReference(): array
    {
        return [
            if_(
                condition: call('isset', [variable('references')->key(variable('value'))]),
                body: throw_(
                    newClass(CircularReferenceFoundDuringNormalization::class, variable('value')),
                )->asStatement(),
            ),
            variable('references')->assign(clone_(variable('references')))->asStatement(),
            variable('references')->key(variable('value'))->assign(variable('value'))->asStatement(),
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
    private function valuesNode(Node $valueNode): Node
    {
        $allPropertiesArePublic = true;
        $assignments = [];

        foreach ($this->class->properties as $property) {
            $assignments[$property->name] = $valueNode->access($property->name);

            $allPropertiesArePublic = $allPropertiesArePublic && $property->isPublic;
        }

        if ($allPropertiesArePublic) {
            return array_($assignments);
        }

        return shortClosure(
            return: call(
                name: 'get_object_vars',
                arguments: [this()],
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
