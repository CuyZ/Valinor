<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use BackedEnum;
use Closure;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\AttributesAggregate;
use CuyZ\Valinor\Definition\AttributesContainer;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTimeInterface;
use Generator;
use stdClass;
use UnitEnum;
use WeakMap;

/** @internal */
final class RecursiveNormalizer implements Normalizer
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private TransformersHandler $transformers,
    ) {}

    public function normalize(mixed $value): mixed
    {
        return $this->doNormalize($value, new WeakMap()); // @phpstan-ignore-line
    }

    /**
     * @param WeakMap<object, true> $references
     */
    private function doNormalize(mixed $value, WeakMap $references, ?Attributes $attributes = null): mixed
    {
        if ($value instanceof AlreadyNormalizedValue) {
            return $value->value;
        }

        if (is_object($value)) {
            if (isset($references[$value])) {
                throw new CircularReferenceFoundDuringNormalization($value);
            }

            // @infection-ignore-all
            $references[$value] = true;
        }

        if (is_object($value)) {
            $classAttributes = $this->classDefinitionRepository->for(NativeClassType::for($value::class))->attributes();

            $attributes = $attributes
                ? new AttributesAggregate($attributes, $classAttributes)
                : $classAttributes;
        }

        // @infection-ignore-all / This is just an optimization, this is not tested.
        $value = $this->transformers->count() === 0
            ? $this->defaultTransformer($value, $references)
            : $this->transformers->transform(
                $value,
                $attributes ?? AttributesContainer::empty(),
                fn (mixed $value) => $this->defaultTransformer($value, $references),
            );

        if (is_array($value)) {
            $value = array_map(
                fn (mixed $value) => $this->doNormalize($value, $references, null),
                $value,
            );
        }

        return $value;
    }

    /**
     * @param WeakMap<object, true> $references
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
                return (array)$value;
            }

            $values = (fn () => get_object_vars($this))->call($value);
            $class = $this->classDefinitionRepository->for(NativeClassType::for($value::class));

            foreach ($values as $key => $subValue) {
                $attributes = $class->properties()->get($key)->attributes();

                $values[$key] = new AlreadyNormalizedValue($this->doNormalize($subValue, $references, $attributes));
            }

            return $values;
        }

        if (is_iterable($value)) {
            if (! is_array($value)) {
                $value = iterator_to_array($value);
            }

            return $value;
        }

        throw new TypeUnhandledByNormalizer($value);
    }
}
