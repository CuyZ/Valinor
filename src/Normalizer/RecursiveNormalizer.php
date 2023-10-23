<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use BackedEnum;
use Closure;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use DateTimeInterface;
use Generator;
use stdClass;
use UnitEnum;
use WeakMap;

use function array_filter;
use function array_shift;
use function is_array;
use function is_iterable;
use function is_object;
use function is_scalar;
use function iterator_to_array;

/** @internal */
final class RecursiveNormalizer implements Normalizer
{
    public function __construct(private FunctionsContainer $transformers) {}

    public function normalize(mixed $value): mixed
    {
        /** @var WeakMap<object, true> $references */
        $references = new WeakMap();

        return $this->doNormalize($value, $references);
    }

    /**
     * @param WeakMap<object, true> $references
     */
    private function doNormalize(mixed $value, WeakMap $references): mixed
    {
        if (is_object($value)) {
            if (isset($references[$value])) {
                throw new CircularReferenceFoundDuringNormalization($value);
            }

            $references[$value] = true;
        }

        if ($this->transformers->count() === 0) {
            $value = $this->defaultTransformer($value);
        } else {
            $transformers = array_filter(
                [...$this->transformers],
                fn (FunctionObject $function) => $function->definition()->parameters()->at(0)->type()->accepts($value),
            );

            $value = $this->nextTransformer($transformers, $value)();
        }

        if (is_array($value)) {
            $value = array_map(
                fn (mixed $value) => $this->doNormalize($value, $references),
                $value,
            );
        }

        return $value;
    }

    /**
     * @param array<FunctionObject> $transformers
     */
    private function nextTransformer(array $transformers, mixed $value): callable
    {
        if ($transformers === []) {
            return fn () => $this->defaultTransformer($value);
        }

        $transformer = array_shift($transformers);
        $arguments = [
            $value,
            fn () => $this->nextTransformer($transformers, $value)(),
        ];

        return fn () => ($transformer->callback())(...$arguments);
    }

    private function defaultTransformer(mixed $value): mixed
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

            return (fn () => get_object_vars($this))->call($value);
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
