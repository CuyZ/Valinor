<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use BackedEnum;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTimeInterface;
use Generator;
use RuntimeException;
use stdClass;
use UnitEnum;

use function array_filter;
use function array_shift;
use function is_array;
use function is_iterable;
use function is_object;
use function is_scalar;
use function iterator_to_array;
use function spl_object_id;

/** @internal */
final class RecursiveNormalizer implements Normalizer
{
    public function __construct(private FunctionsContainer $handlers) {}

    public function normalize(mixed $value): mixed
    {
        return $this->doNormalize($value, []);
    }

    /**
     * @param array<int, true> $references
     */
    private function doNormalize(mixed $value, array $references): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }

        if (is_object($value) && ! $value instanceof Generator) {
            $id = spl_object_id($value);

            if (isset($references[$id])) {
                throw new \RuntimeException('@todo'); // @todo
            }

            $references[$id] = true;

            return $this->doNormalize($this->normalizeObject($value), $references);
        }

        if (is_iterable($value)) {
            if (! is_array($value)) {
                $value = iterator_to_array($value);
            }

            return array_map(
                fn (mixed $value) => $this->doNormalize($value, $references),
                $value
            );
        }

        throw new RuntimeException('@todo unhandled type'); // @todo
    }

    private function normalizeObject(object $object): mixed
    {
        if ($this->handlers->count() === 0) {
            return ($this->defaultObjectNormalizer($object))();
        }

        $type = new NativeClassType($object::class);

        $handlers = array_filter(
            [...$this->handlers],
            fn (FunctionObject $function) => $type->matches($function->definition()->parameters()->at(0)->type())
        );

        return $this->nextNormalizer($handlers, $object)();
    }

    /**
     * @param array<FunctionObject> $handlers
     */
    private function nextNormalizer(array $handlers, object $object): callable
    {
        if ($handlers === []) {
            return $this->defaultObjectNormalizer($object);
        }

        $handler = array_shift($handlers);
        $arguments = [
            $object,
            fn () => $this->nextNormalizer($handlers, $object)(),
        ];

        return fn () => ($handler->callback())(...$arguments);
    }

    private function defaultObjectNormalizer(object $object): callable
    {
        if ($object instanceof UnitEnum) {
            return fn () => $object instanceof BackedEnum ? $object->value : $object->name;
        }

        if ($object instanceof DateTimeInterface) {
            return fn () => $object->format('Y-m-d\\TH:i:s.uP'); // RFC 3339
        }

        if ($object::class === stdClass::class) {
            return fn () => (array)$object;
        }

        return fn () => (fn () => get_object_vars($this))->call($object);
    }
}
