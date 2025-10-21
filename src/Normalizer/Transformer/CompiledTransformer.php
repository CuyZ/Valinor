<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use Closure;
use CuyZ\Valinor\Cache\Cache;
use CuyZ\Valinor\Cache\CacheEntry;
use CuyZ\Valinor\Cache\TypeFilesWatcher;
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
use IteratorAggregate;
use UnitEnum;

use function array_is_list;
use function assert;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_iterable;
use function is_null;
use function is_object;
use function is_scalar;
use function is_string;
use function reset;

/** @internal */
final class CompiledTransformer implements Transformer
{
    public function __construct(
        private TransformerDefinitionBuilder $definitionBuilder,
        private TypeFilesWatcher $typeFilesWatcher,
        /** @var Cache<Transformer> */
        private Cache $cache,
        /** @var list<callable> */
        private array $transformers,
    ) {}

    public function transform(mixed $value): mixed
    {
        $type = $this->inferType($value, isSure: true);

        $key = "transformer-\0" . $type->toString();

        $transformer = $this->cache->get($key, $this->transformers, $this);

        if ($transformer) {
            return $transformer->transform($value);
        }

        $code = $this->compileFor($type);
        $filesToWatch = $this->typeFilesWatcher->for($type);

        $this->cache->set($key, new CacheEntry($code, $filesToWatch));

        $transformer = $this->cache->get($key, $this->transformers, $this);

        assert($transformer instanceof Transformer);

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
        return match (true) {
            $value instanceof UnitEnum => EnumType::native($value::class),
            is_object($value) && ! $value instanceof Closure && ! $value instanceof Generator => $this->inferObjectType($value),
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

    private function inferObjectType(object $value): NativeClassType
    {
        if (is_iterable($value)) {
            $iterableType = $this->inferIterableType($value);

            return new NativeClassType($value::class, [$iterableType->subType()]);
        }

        return new NativeClassType($value::class);
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
        }

        if ($value instanceof IteratorAggregate) {
            $value = $value->getIterator();
        }

        if ($value instanceof Iterator && $value->valid()) {
            $firstValueType = $this->inferType($value->current());

            return new IterableType(ArrayKeyType::default(), $firstValueType);
        }

        return IterableType::native();
    }
}
