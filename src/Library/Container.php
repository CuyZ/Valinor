<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use CuyZ\Valinor\Cache\ChainCache;
use CuyZ\Valinor\Cache\KeySanitizerCache;
use CuyZ\Valinor\Cache\RuntimeCache;
use CuyZ\Valinor\Cache\Warmup\RecursiveCacheWarmupService;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\Cache\CacheClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Cache\CacheFunctionDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\NativeAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionFunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\ArgumentsMapper;
use CuyZ\Valinor\Mapper\Object\Factory\CacheObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\CollisionObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\ConstructorObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\DateTimeObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\DateTimeZoneObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\ReflectionObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\StrictTypesObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ArrayNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\CasterNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\CasterProxyNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ErrorCatcherNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\InterfaceNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\IterableNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ListNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\NativeClassNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\NodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ObjectImplementations;
use CuyZ\Valinor\Mapper\Tree\Builder\ObjectNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ScalarNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ShapedArrayNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\StrictNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\UnionNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ValueAlteringNodeBuilder;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Mapper\TypeArgumentsMapper;
use CuyZ\Valinor\Mapper\TypeTreeMapper;
use CuyZ\Valinor\Normalizer\ArrayNormalizer;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Normalizer\Normalizer;
use CuyZ\Valinor\Normalizer\Transformer\KeyTransformersHandler;
use CuyZ\Valinor\Normalizer\Transformer\RecursiveTransformer;
use CuyZ\Valinor\Normalizer\Transformer\ValueTransformersHandler;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use Psr\SimpleCache\CacheInterface;

use function array_keys;
use function call_user_func;
use function count;

/** @internal */
final class Container
{
    /** @var array<class-string, object> */
    private array $services = [];

    /** @var array<class-string, callable(): object> */
    private array $factories;

    public function __construct(Settings $settings)
    {
        $this->factories = [
            TreeMapper::class => fn () => new TypeTreeMapper(
                $this->get(TypeParser::class),
                $this->get(RootNodeBuilder::class)
            ),

            ArgumentsMapper::class => fn () => new TypeArgumentsMapper(
                $this->get(FunctionDefinitionRepository::class),
                $this->get(RootNodeBuilder::class)
            ),

            RootNodeBuilder::class => fn () => new RootNodeBuilder(
                $this->get(NodeBuilder::class)
            ),

            NodeBuilder::class => function () use ($settings) {
                $listNodeBuilder = new ListNodeBuilder($settings->enableFlexibleCasting);
                $arrayNodeBuilder = new ArrayNodeBuilder($settings->enableFlexibleCasting);

                $builder = new CasterNodeBuilder([
                    ListType::class => $listNodeBuilder,
                    NonEmptyListType::class => $listNodeBuilder,
                    ArrayType::class => $arrayNodeBuilder,
                    NonEmptyArrayType::class => $arrayNodeBuilder,
                    IterableType::class => $arrayNodeBuilder,
                    ShapedArrayType::class => new ShapedArrayNodeBuilder($settings->allowSuperfluousKeys),
                    ScalarType::class => new ScalarNodeBuilder($settings->enableFlexibleCasting),
                    ClassType::class => new NativeClassNodeBuilder(
                        $this->get(ClassDefinitionRepository::class),
                        $this->get(ObjectBuilderFactory::class),
                        $this->get(ObjectNodeBuilder::class),
                        $settings->enableFlexibleCasting,
                    ),
                ]);

                $builder = new UnionNodeBuilder(
                    $builder,
                    $this->get(ClassDefinitionRepository::class),
                    $this->get(ObjectBuilderFactory::class),
                    $this->get(ObjectNodeBuilder::class),
                    $settings->enableFlexibleCasting
                );

                $builder = new InterfaceNodeBuilder(
                    $builder,
                    $this->get(ObjectImplementations::class),
                    $this->get(ClassDefinitionRepository::class),
                    $this->get(ObjectBuilderFactory::class),
                    $this->get(ObjectNodeBuilder::class),
                    $settings->enableFlexibleCasting
                );

                $builder = new CasterProxyNodeBuilder($builder);
                $builder = new IterableNodeBuilder($builder);

                if (count($settings->valueModifier) > 0) {
                    $builder = new ValueAlteringNodeBuilder(
                        $builder,
                        new FunctionsContainer(
                            $this->get(FunctionDefinitionRepository::class),
                            $settings->valueModifier
                        )
                    );
                }

                $builder = new StrictNodeBuilder($builder, $settings->allowPermissiveTypes, $settings->enableFlexibleCasting);

                return new ErrorCatcherNodeBuilder($builder, $settings->exceptionFilter);
            },

            ObjectNodeBuilder::class => fn () => new ObjectNodeBuilder($settings->allowSuperfluousKeys),

            ObjectImplementations::class => fn () => new ObjectImplementations(
                new FunctionsContainer(
                    $this->get(FunctionDefinitionRepository::class),
                    $settings->inferredMapping
                ),
                $this->get(TypeParser::class),
            ),

            ObjectBuilderFactory::class => function () use ($settings) {
                $constructors = new FunctionsContainer(
                    $this->get(FunctionDefinitionRepository::class),
                    $settings->customConstructors
                );

                $factory = new ReflectionObjectBuilderFactory();
                $factory = new ConstructorObjectBuilderFactory($factory, $settings->nativeConstructors, $constructors);
                $factory = new DateTimeZoneObjectBuilderFactory($factory, $this->get(FunctionDefinitionRepository::class));
                $factory = new DateTimeObjectBuilderFactory($factory, $settings->supportedDateFormats, $this->get(FunctionDefinitionRepository::class));
                $factory = new CollisionObjectBuilderFactory($factory);

                if (! $settings->allowPermissiveTypes) {
                    $factory = new StrictTypesObjectBuilderFactory($factory);
                }

                /** @var RuntimeCache<list<ObjectBuilder>> $cache */
                $cache = new RuntimeCache();

                return new CacheObjectBuilderFactory($factory, $cache);
            },

            RecursiveTransformer::class => fn () => new RecursiveTransformer(
                $this->get(ClassDefinitionRepository::class),
                new ValueTransformersHandler(
                    $this->get(FunctionDefinitionRepository::class),
                ),
                new KeyTransformersHandler(
                    $this->get(FunctionDefinitionRepository::class),
                ),
                $settings->transformersSortedByPriority(),
                array_keys($settings->transformerAttributes),
            ),

            ArrayNormalizer::class => fn () => new ArrayNormalizer(
                $this->get(RecursiveTransformer::class),
            ),

            ClassDefinitionRepository::class => fn () => new CacheClassDefinitionRepository(
                new ReflectionClassDefinitionRepository(
                    $this->get(TypeParserFactory::class),
                    $this->get(AttributesRepository::class),
                ),
                $this->get(CacheInterface::class),
            ),

            FunctionDefinitionRepository::class => fn () => new CacheFunctionDefinitionRepository(
                new ReflectionFunctionDefinitionRepository(
                    $this->get(TypeParserFactory::class),
                    $this->get(AttributesRepository::class),
                ),
                $this->get(CacheInterface::class)
            ),

            AttributesRepository::class => fn () => new NativeAttributesRepository(),

            TypeParserFactory::class => fn () => new LexingTypeParserFactory(),

            TypeParser::class => fn () => $this->get(TypeParserFactory::class)->get(),

            RecursiveCacheWarmupService::class => fn () => new RecursiveCacheWarmupService(
                $this->get(TypeParser::class),
                $this->get(CacheInterface::class),
                $this->get(ObjectImplementations::class),
                $this->get(ClassDefinitionRepository::class),
                $this->get(ObjectBuilderFactory::class)
            ),

            CacheInterface::class => function () use ($settings) {
                $cache = new RuntimeCache();

                if (isset($settings->cache)) {
                    $cache = new ChainCache($cache, new KeySanitizerCache($settings->cache));
                }

                return $cache;
            },
        ];
    }

    public function treeMapper(): TreeMapper
    {
        return $this->get(TreeMapper::class);
    }

    public function argumentsMapper(): ArgumentsMapper
    {
        return $this->get(ArgumentsMapper::class);
    }

    /**
     * @template T of Normalizer
     *
     * @param Format<T> $format
     * @return T
     */
    public function normalizer(Format $format): Normalizer
    {
        return $this->get($format->type());
    }

    public function cacheWarmupService(): RecursiveCacheWarmupService
    {
        return $this->get(RecursiveCacheWarmupService::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $name
     * @return T
     */
    private function get(string $name): object
    {
        return $this->services[$name] ??= call_user_func($this->factories[$name]); // @phpstan-ignore-line
    }
}
