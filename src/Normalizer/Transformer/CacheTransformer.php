<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use Closure;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerCompiler;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NullType;
use Generator;
use Iterator;
use Psr\SimpleCache\CacheInterface;
use UnitEnum;

use function is_array;
use function is_iterable;
use function is_object;

/** @internal */
final class CacheTransformer implements Transformer
{
    public function __construct(
        private Transformer $delegate,
        private TransformerCompiler $compiler,
        /** @var CacheInterface<Transformer|callable(list<callable>, Transformer): Transformer> */
        private CacheInterface $cache,
        /** @var list<callable> */
        private array $transformers,
    ) {}

    public function transform(mixed $value, Formatter $formatter): mixed
    {
        $type = $this->inferType($value);

        $key = "transformer-\0" . $type->toString();

        $entry = $this->cache->get($key);

        if ($entry) {
            $transformer = $entry instanceof Transformer ? $entry : $entry($this->transformers, $this);

            return $transformer->transform($value, $formatter);
        }

        $compilationCallback = fn () => $this->compiler->compileFor($type, $formatter);

        $transformer = new EvaluatedTransformer($this->delegate, $compilationCallback);

        $this->cache->set($key, $transformer);

        return $this->delegate->transform($value, $formatter);
    }

    private function inferType(mixed $value): Type
    {
        return match (true) {
            $value instanceof UnitEnum => EnumType::native($value::class),
            is_object($value) && ! $value instanceof Closure && ! $value instanceof Generator => new NativeClassType($value::class),
            is_iterable($value) => $this->inferIterableType($value),
            is_string($value) => NativeStringType::get(),
            is_int($value) => NativeIntegerType::get(),
            is_float($value) => NativeFloatType::get(),
            is_bool($value) => NativeBooleanType::get(),
            is_null($value) => NullType::get(),
            default => throw new TypeUnhandledByNormalizer($value),
        };
    }

    /**
     * This method contains a strongly opinionated rule: when normalizing an
     * iterable, we assume that the iterable has a high probability of
     * containing only one type of value, each iteration matching the type of
     * the first value.
     *
     * This is a trade-off between performance and accuracy: the first value
     * will always be normalized, so we can safely add its type to the compiling
     * process. For other values, if their types match the first one, that is a
     * big performance win; if they don't, the transformer will do its best to
     * find an optimized way of dealing with it or, by default, fall back to
     * the delegate transformer.
     *
     * @param iterable<mixed> $value
     */
    private function inferIterableType(iterable $value): CompositeTraversableType
    {
        if (is_array($value)) {
            // @todo
//            $firstValueType = $this->inferType(reset($value));
//
//            return ArrayType::simple(new UnionType($firstValueType, MixedType::get()));
            return ArrayType::simple(MixedType::get());
        } elseif ($value instanceof Iterator) {
            $firstValueType = $this->inferType($value->current());

            return new IterableType(ArrayKeyType::default(), MixedType::get());
        }

        return IterableType::native();
    }
}
