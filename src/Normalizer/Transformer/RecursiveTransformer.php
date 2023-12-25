<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use BackedEnum;
use Closure;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTimeInterface;
use Generator;
use ReflectionClass;
use stdClass;
use UnitEnum;
use WeakMap;

use function array_map;
use function array_reverse;
use function get_object_vars;
use function is_array;
use function is_iterable;

/**  @internal */
final class RecursiveTransformer
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private ValueTransformersHandler $valueTransformers,
        private KeyTransformersHandler $keyTransformers,
        /** @var list<callable> */
        private array $transformers,
        /** @var list<class-string> */
        private array $transformerAttributes,
    ) {}

    /**
     * @return array<mixed>|scalar|null
     */
    public function transform(mixed $value): mixed
    {
        return $this->doTransform($value, new WeakMap()); // @phpstan-ignore-line
    }

    /**
     * @param WeakMap<object, true> $references
     * @param list<object> $attributes
     * @return iterable<mixed>|scalar|null
     */
    private function doTransform(mixed $value, WeakMap $references, array $attributes = []): mixed
    {
        if (is_object($value)) {
            if (isset($references[$value])) {
                throw new CircularReferenceFoundDuringNormalization($value);
            }

            // @infection-ignore-all
            $references[$value] = true;
        }

        if ($this->transformers === [] && $this->transformerAttributes === []) {
            return $this->defaultTransformer($value, $references);
        }

        if ($this->transformerAttributes !== [] && is_object($value)) {
            $classAttributes = $this->classDefinitionRepository->for(NativeClassType::for($value::class))->attributes();

            $attributes = [...$attributes, ...$classAttributes];
        }

        return $this->valueTransformers->transform(
            $value,
            $attributes,
            $this->transformers,
            fn (mixed $value) => $this->defaultTransformer($value, $references),
        );
    }

    /**
     * @param WeakMap<object, true> $references
     * @return iterable<mixed>|scalar|null
     */
    private function defaultTransformer(mixed $value, WeakMap $references): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }

        if (is_object($value) && ! $value instanceof Closure && ! $value instanceof Generator) {
            if ($value instanceof UnitEnum) {
                return $value instanceof BackedEnum ? $value->value : $value->name;
            }

            if ($value instanceof DateTimeInterface) {
                return $value->format('Y-m-d\\TH:i:s.uP'); // RFC 3339
            }

            if ($value::class === stdClass::class) {
                return array_map(
                    fn (mixed $value) => $this->doTransform($value, $references),
                    (array)$value
                );
            }

            $values = (fn () => get_object_vars($this))->call($value);

            // @infection-ignore-all
            if (PHP_VERSION_ID < 8_01_00) {
                // In PHP 8.1, behavior changed for `get_object_vars` function:
                // the sorting order was taking children properties first, now
                // it takes parents properties first. This code is a temporary
                // workaround to keep the same behavior in PHP 8.0 and later
                // versions.
                $sorted = [];

                $parents = array_reverse(class_parents($value));
                $parents[] = $value::class;

                foreach ($parents as $parent) {
                    foreach ((new ReflectionClass($parent))->getProperties() as $property) {
                        if (! isset($values[$property->name])) {
                            continue;
                        }

                        $sorted[$property->name] = $values[$property->name];
                    }
                }

                $values = $sorted;
            }

            $transformed = [];

            $class = $this->classDefinitionRepository->for(NativeClassType::for($value::class));

            foreach ($values as $key => $subValue) {
                $attributes = $this->filterAttributes($class->properties()->get($key)->attributes());

                $key = $this->keyTransformers->transformKey($key, $attributes);

                $transformed[$key] = $this->doTransform($subValue, $references, $attributes);
            }

            return $transformed;
        }

        if (is_iterable($value)) {
            if (is_array($value)) {
                return array_map(
                    fn (mixed $value) => $this->doTransform($value, $references),
                    $value
                );
            }

            return (function () use ($value, $references) {
                foreach ($value as $key => $item) {
                    yield $key => $this->doTransform($item, $references);
                }
            })();
        }

        throw new TypeUnhandledByNormalizer($value);
    }

    /**
     * @return list<object>
     */
    private function filterAttributes(Attributes $attributes): array
    {
        $filteredAttributes = [];

        foreach ($attributes as $attribute) {
            foreach ($this->transformerAttributes as $transformerAttribute) {
                if ($attribute instanceof $transformerAttribute) {
                    $filteredAttributes[] = $attribute;
                    break;
                }
            }
        }

        return $filteredAttributes;
    }
}
