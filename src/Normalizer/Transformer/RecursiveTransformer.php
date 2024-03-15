<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use ArrayObject;
use BackedEnum;
use Closure;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use stdClass;
use UnitEnum;
use WeakMap;

use function array_map;
use function get_object_vars;
use function is_a;
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
     * @param list<AttributeDefinition> $attributes
     * @return iterable<mixed>|scalar|null
     */
    private function doTransform(mixed $value, WeakMap $references, array $attributes = []): mixed
    {
        if (is_object($value)) {
            if (isset($references[$value])) {
                throw new CircularReferenceFoundDuringNormalization($value);
            }

            $references = clone $references;

            // @infection-ignore-all
            $references[$value] = true;
        }

        if (is_object($value)) {
            $classAttributes = $this->classDefinitionRepository->for(new NativeClassType($value::class))->attributes;
            $classAttributes = $this->filterAttributes($classAttributes);

            $attributes = [...$attributes, ...$classAttributes];
        }

        if ($this->transformers === [] && $attributes === []) {
            return $this->defaultTransformer($value, $references);
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

            if ($value instanceof DateTimeZone) {
                return $value->getName();
            }

            if ($value::class === stdClass::class || $value instanceof ArrayObject) {
                return array_map(
                    fn (mixed $value) => $this->doTransform($value, $references),
                    (array)$value
                );
            }

            $values = (fn () => get_object_vars($this))->call($value);

            $transformed = [];

            $class = $this->classDefinitionRepository->for(new NativeClassType($value::class));

            foreach ($values as $key => $subValue) {
                $attributes = $this->filterAttributes($class->properties->get($key)->attributes)->toArray();

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

    private function filterAttributes(Attributes $attributes): Attributes
    {
        return $attributes->filter(function (AttributeDefinition $attribute) {
            if ($attribute->class->attributes->has(AsTransformer::class)) {
                return true;
            }

            foreach ($this->transformerAttributes as $transformerAttribute) {
                if (is_a($attribute->class->type->className(), $transformerAttribute, true)) {
                    return true;
                }
            }

            return false;
        });
    }
}
