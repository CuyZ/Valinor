<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use Closure;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerRootNode;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\NullType;
use Generator;
use Iterator;
use Psr\SimpleCache\CacheInterface;
use UnitEnum;

use function array_is_list;
use function is_array;
use function is_iterable;
use function is_object;
use function is_scalar;

/** @internal */
final class CacheTransformer implements Transformer
{
    public function __construct(
        private TransformerDefinitionBuilder $definitionBuilder,
        /** @var CacheInterface<callable(list<callable>, Transformer): Transformer> */
        private CacheInterface $cache,
        /** @var list<callable> */
        private array $transformers,
    ) {}

    public function transform(mixed $value): mixed
    {
        $type = $this->inferType($value, isSure: true);

        $key = "transformer-\0" . $type->toString();

        $entry = $this->cache->get($key);

        if ($entry) {
            $transformer = $entry($this->transformers, $this);

            return $transformer->transform($value);
        }

        $transformer = new EvaluatedTransformer($this->compileFor($type));

        // @phpstan-ignore argument.type (this is a temporary workaround, while waiting for the cache API to be refined)
        $this->cache->set($key, $transformer);

        $entry = $this->cache->get($key);

        // @phpstan-ignore callable.nonCallable (this is a temporary workaround, while waiting for the cache API to be refined)
        $transformer = $entry($this->transformers, $this);

        return $transformer->transform($value);
    }

    private function compileFor(Type $type): string
    {
        $rootNode = new TransformerRootNode($this->definitionBuilder, $type);

        $node = Node::shortClosure($rootNode)
            ->witParameters(
                Node::parameterDeclaration('transformers', 'array'),
                Node::parameterDeclaration('delegate', Transformer::class),
            );

        return (new Compiler())->compile($node)->code();
    }

    private function inferType(mixed $value, bool $isSure = false): Type
    {
        // @infection-ignore-all (mutation from `true` to `false` is useless)
        return match (true) {
            $value instanceof UnitEnum => EnumType::native($value::class),
            is_object($value) && ! $value instanceof Closure && ! $value instanceof Generator => new NativeClassType($value::class),
            is_iterable($value) => $this->inferIterableType($value),
            is_scalar($value) && $isSure => ValueTypeFactory::from($value),
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
            if ($value === []) {
                return ArrayType::native();
            }

            $firstValueType = $this->inferType(reset($value));

            if (array_is_list($value)) {
                return new NonEmptyListType($firstValueType);
            }

            return new NonEmptyArrayType(ArrayKeyType::default(), $firstValueType);
        } elseif ($value instanceof Iterator && $value->valid()) {
            $firstValueType = $this->inferType($value->current());

            return new IterableType(ArrayKeyType::default(), $firstValueType);
        }

        return IterableType::native();
    }
}
