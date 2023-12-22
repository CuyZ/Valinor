<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use BackedEnum;
use Closure;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\KeyTransformersHandler;
use CuyZ\Valinor\Normalizer\Transformer\ValueTransformersHandler;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTimeInterface;
use Generator;
use stdClass;
use UnitEnum;
use WeakMap;

use function array_map;

/**  @internal */
final class RecursiveNormalizer
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
     * @param Formatter<mixed> $formatter
     */
    public function normalize(mixed $value, Formatter $formatter): mixed
    {
        $this->doNormalize($value, $formatter, new WeakMap()); // @phpstan-ignore-line

        return $formatter->value();
    }

    /**
     * @param Formatter<mixed> $formatter
     * @param WeakMap<object, true> $references
     * @param list<object> $attributes
     * @return iterable<mixed>|scalar|null
     */
    private function doNormalize(mixed $value, Formatter $formatter, WeakMap $references, array $attributes = []): mixed
    {
        if (is_object($value)) {
            if (isset($references[$value])) {
                throw new CircularReferenceFoundDuringNormalization($value);
            }

            // @infection-ignore-all
            $references[$value] = true;
        }

        if ($this->transformers === [] && $this->transformerAttributes === []) {
            $value = $this->defaultTransformer($value, $formatter, $references);

            $formatter->push($value);

            return $value;
        }

        if ($this->transformerAttributes !== [] && is_object($value)) {
            $classAttributes = $this->classDefinitionRepository->for(NativeClassType::for($value::class))->attributes();

            $attributes = [...$attributes, ...$classAttributes];
        }

        $value = $this->valueTransformers->transform(
            $value,
            $attributes,
            $this->transformers,
            fn (mixed $value) => $this->defaultTransformer($value, $formatter, $references),
        );

        $formatter->push($value);

        return $value;
    }

    /**
     * @param Formatter<mixed> $formatter
     * @param WeakMap<object, true> $references
     * @return iterable<mixed>|scalar|null
     */
    private function defaultTransformer(mixed $value, Formatter $formatter, WeakMap $references): mixed
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
                    fn (mixed $value) => $this->doNormalize($value, $formatter, $references),
                    (array)$value
                );
            }

            $values = (fn () => get_object_vars($this))->call($value);
            $transformed = [];

            $class = $this->classDefinitionRepository->for(NativeClassType::for($value::class));

            foreach ($values as $key => $subValue) {
                $attributes = $this->filterAttributes($class->properties()->get($key)->attributes());

                $key = $this->keyTransformers->transformKey($key, $attributes);

                $transformed[$key] = $this->doNormalize($subValue, $formatter, $references, $attributes);
            }

            return $transformed;
        }

        if (is_iterable($value)) {
            return (function () use ($value, $formatter, $references) {
                foreach ($value as $key => $item) {
                    yield $key => $this->doNormalize($item, $formatter, $references);
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
