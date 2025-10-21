<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use BackedEnum;
use Closure;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use DateTimeInterface;
use DateTimeZone;
use stdClass;
use UnitEnum;
use WeakMap;

use function array_map;
use function array_shift;
use function call_user_func;
use function get_object_vars;
use function is_array;
use function is_iterable;
use function is_object;
use function is_scalar;

/**  @internal */
final class RecursiveTransformer implements Transformer
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private FunctionDefinitionRepository $functionDefinitionRepository,
        private TransformerContainer $transformerContainer,
    ) {}

    public function transform(mixed $value): mixed
    {
        return $this->doTransform($value, new WeakMap()); // @phpstan-ignore-line
    }

    /**
     * @param WeakMap<object, object> $references
     * @param list<AttributeDefinition> $attributes
     */
    private function doTransform(mixed $value, WeakMap $references, array $attributes = []): mixed
    {
        if (is_object($value)) {
            if (isset($references[$value])) {
                throw new CircularReferenceFoundDuringNormalization($value);
            }

            $references = clone $references;
            $references[$value] = $value;

            $type = $value instanceof UnitEnum
                ? EnumType::native($value::class)
                : new NativeClassType($value::class);

            $classAttributes = $this->classDefinitionRepository->for($type)->attributes->toArray();

            $attributes = [...$attributes, ...$classAttributes];
        }

        if (! $this->transformerContainer->hasTransformers() && $attributes === []) {
            return $this->defaultTransformer($value, $references);
        }

        if ($attributes !== []) {
            $attributes = (new Attributes(...$attributes))
                ->filter(TransformerContainer::filterTransformerAttributes(...))
                ->toArray();
        }

        $transformers = [
            // First chunk of transformers to be used: attributes, coming from
            // class or property.
            ...array_map(
                fn (AttributeDefinition $attribute) => $attribute->instantiate()->normalize(...), // @phpstan-ignore-line / We know the method exists
                $attributes,
            ),
            // Second chunk of transformers to be used: registered transformers.
            ...$this->transformerContainer->transformers(),
            // Last one: default transformer.
            fn (mixed $value) => $this->defaultTransformer($value, $references),
        ];

        return call_user_func($this->nextTransformer($transformers, $value));
    }

    /**
     * @param non-empty-list<callable> $transformers
     */
    private function nextTransformer(array $transformers, mixed $value): callable
    {
        $transformer = array_shift($transformers);

        if ($transformers === []) {
            return fn () => $transformer($value);
        }

        $function = $this->functionDefinitionRepository->for($transformer);

        if (! $function->parameters->at(0)->type->accepts($value)) {
            return $this->nextTransformer($transformers, $value);
        }

        return fn () => $transformer($value, fn () => call_user_func($this->nextTransformer($transformers, $value)));
    }

    /**
     * @param WeakMap<object, object> $references
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

        if (is_object($value) && ! $value instanceof Closure) {
            if ($value instanceof UnitEnum) {
                return $value instanceof BackedEnum ? $value->value : $value->name;
            }

            if ($value instanceof DateTimeInterface) {
                return $value->format('Y-m-d\\TH:i:s.uP'); // RFC 3339
            }

            if ($value instanceof DateTimeZone) {
                return $value->getName();
            }

            if ($value::class === stdClass::class) {
                $result = (array)$value;

                if ($result === []) {
                    return EmptyObject::get();
                }

                return array_map(
                    fn (mixed $value) => $this->doTransform($value, $references),
                    $result,
                );
            }

            $values = (fn () => get_object_vars($this))->call($value);

            $transformed = [];

            $class = $this->classDefinitionRepository->for(new NativeClassType($value::class));

            foreach ($values as $key => $subValue) {
                $property = $class->properties->get($key);

                $keyTransformersAttributes = $property->attributes->filter(TransformerContainer::filterKeyTransformerAttributes(...));

                foreach ($keyTransformersAttributes as $attribute) {
                    $method = $attribute->class->methods->get('normalizeKey');

                    if ($method->parameters->count() === 0 || $method->parameters->at(0)->type->accepts($key)) {
                        /** @var string $key */
                        $key = $attribute->instantiate()->normalizeKey($key); // @phpstan-ignore-line / We know the method exists
                    }
                }

                $transformed[$key] = $this->doTransform($subValue, $references, $property->attributes->toArray());
            }

            return $transformed;
        }

        throw new TypeUnhandledByNormalizer($value);
    }
}
