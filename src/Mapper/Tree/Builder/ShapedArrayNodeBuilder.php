<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedKeyInSource;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use Throwable;

use function array_diff;
use function array_diff_key;
use function array_key_exists;
use function array_keys;
use function array_values;
use function assert;
use function is_array;
use function is_iterable;
use function iterator_to_array;

/** @internal */
final class ShapedArrayNodeBuilder implements NodeBuilder
{
    public function __construct(
        private ClassDefinitionRepository|null $classDefinitionRepository = null,
    ) {}
    public function build(Shell $shell): Node
    {
        $type = $shell->type;
        $value = $shell->value();

        assert($type instanceof ShapedArrayType);

        if (! is_iterable($value)) {
            return $shell->error(new SourceMustBeIterable($value));
        }

        if (! is_array($value)) {
            $value = iterator_to_array($value);
        }

        $children = [];
        $errors = [];

        // First phase: we loop through all the shaped array elements and try
        // to find corresponding value in the source value to build them.
        foreach ($type->elements as $key => $element) {
            $child = $shell
                ->child((string)$key, $element->type())
                ->withAttributes($element->attributes());

            $consumedKeys = [];
            if (array_key_exists($key, $value)) {
                $result = $this->resolveNestedKeys($key, $value, $element->type());
                $child = $child->withValue($result['value']);
                $consumedKeys = $result['consumed_keys'];
            } elseif ($element->isOptional()) {
                continue;
            }

            $child = $child->build();

            if ($child->isValid()) {
                $children[$key] = $child->value();
            } else {
                $errors[] = $child;
            }

            unset($value[$key]);
            foreach ($consumedKeys as $consumedKey) {
                unset($value[$consumedKey]);
            }
        }

        // Second phase: if the shaped array is unsealed, we take the remaining
        // values from the source and try to build them.
        if ($type->isUnsealed) {
            $unsealedNode = $shell
                ->withType($type->unsealedType())
                ->withValue($value)
                ->build();

            if ($unsealedNode->isValid()) {
                // @phpstan-ignore assignOp.invalid (we know value is an array)
                $children += $unsealedNode->value();
            } else {
                $errors[] = $unsealedNode;
            }
        } elseif (! $shell->allowSuperfluousKeys) {
            // Third phase: the superfluous keys are not allowed, so we add an
            // error for each remaining key in the source.
            $diff = array_diff_key($value, $children, $shell->allowedSuperfluousKeys);

            foreach ($diff as $key => $val) {
                $errors[] = $shell
                    ->child((string)$key, UnresolvableType::forSuperfluousValue((string)$key))
                    ->withValue($val)
                    ->error(new UnexpectedKeyInSource((string)$key));
            }
        }

        if ($errors === []) {
            return $shell->node($children);
        }

        return $shell->errors($errors);
    }

    /**
     * @param array<mixed> $parentValue
     * @return array{value: mixed, consumed_keys: list<string>}
     */
    private function resolveNestedKeys(int|string $key, array $parentValue, mixed $type): array
    {
        if (! $type instanceof ObjectType || $this->classDefinitionRepository === null) {
            return ['value' => $parentValue[$key], 'consumed_keys' => []];
        }

        if (is_array($parentValue[$key])) {
            return ['value' => $parentValue[$key], 'consumed_keys' => []];
        }

        try {
            $classDefinition = $this->classDefinitionRepository->for($type);
            $parentKeys = array_keys($parentValue);

            foreach ($classDefinition->methods as $method) {
                if ($method->name !== '__construct'
                    && !$method->attributes->has(\CuyZ\Valinor\Mapper\Object\Constructor::class)) {
                    continue;
                }

                $paramNames = [];
                $hasAllRequired = true;

                foreach ($method->parameters as $parameter) {
                    $paramNames[] = $parameter->name;
                    if (!$parameter->isOptional && !array_key_exists($parameter->name, $parentValue)) {
                        $hasAllRequired = false;
                    }
                }

                if ($hasAllRequired) {
                    // Extract only the keys needed for this nested object
                    $nestedValue = [];
                    foreach ($paramNames as $paramName) {
                        if (array_key_exists($paramName, $parentValue)) {
                            $nestedValue[$paramName] = $parentValue[$paramName];
                        }
                    }

                    return [
                        'value' => $nestedValue,
                        'consumed_keys' => array_values(array_diff($paramNames, [$key]))
                    ];
                }
            }
        } catch (Throwable) {
            return ['value' => $parentValue[$key], 'consumed_keys' => []];
        }

        return ['value' => $parentValue[$key], 'consumed_keys' => []];
    }
}
