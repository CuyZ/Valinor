<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use CuyZ\Valinor\Cache\RuntimeCache;
use CuyZ\Valinor\Cache\Warmup\RecursiveCacheWarmupService;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
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
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use Psr\SimpleCache\CacheInterface;

use function call_user_func;
use function count;

/** @internal */
final class MapperContainer
{
    private SharedContainer $shared;

    /** @var array<class-string, object> */
    private array $services = [];

    /** @var array<class-string, callable(): object> */
    private array $factories;

    public function __construct(MapperSettings $settings)
    {
        $this->shared = SharedContainer::new($settings->cache ?? null);
        $this->factories = [
            TreeMapper::class => fn () => new TypeTreeMapper(
                $this->shared->get(TypeParser::class),
                $this->get(RootNodeBuilder::class)
            ),

            ArgumentsMapper::class => fn () => new TypeArgumentsMapper(
                $this->shared->get(FunctionDefinitionRepository::class),
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
                        $this->shared->get(ClassDefinitionRepository::class),
                        $this->get(ObjectBuilderFactory::class),
                        $this->get(ObjectNodeBuilder::class),
                        $settings->enableFlexibleCasting,
                    ),
                ]);

                $builder = new UnionNodeBuilder(
                    $builder,
                    $this->shared->get(ClassDefinitionRepository::class),
                    $this->get(ObjectBuilderFactory::class),
                    $this->get(ObjectNodeBuilder::class),
                    $settings->enableFlexibleCasting
                );

                $builder = new InterfaceNodeBuilder(
                    $builder,
                    $this->get(ObjectImplementations::class),
                    $this->shared->get(ClassDefinitionRepository::class),
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
                            $this->shared->get(FunctionDefinitionRepository::class),
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
                    $this->shared->get(FunctionDefinitionRepository::class),
                    $settings->inferredMapping
                ),
                $this->shared->get(TypeParser::class),
            ),

            ObjectBuilderFactory::class => function () use ($settings) {
                $constructors = new FunctionsContainer(
                    $this->shared->get(FunctionDefinitionRepository::class),
                    $settings->customConstructors
                );

                $factory = new ReflectionObjectBuilderFactory();
                $factory = new ConstructorObjectBuilderFactory($factory, $settings->nativeConstructors, $constructors);
                $factory = new DateTimeZoneObjectBuilderFactory($factory, $this->shared->get(FunctionDefinitionRepository::class));
                $factory = new DateTimeObjectBuilderFactory($factory, $settings->supportedDateFormats, $this->shared->get(FunctionDefinitionRepository::class));
                $factory = new CollisionObjectBuilderFactory($factory);

                if (! $settings->allowPermissiveTypes) {
                    $factory = new StrictTypesObjectBuilderFactory($factory);
                }

                /** @var RuntimeCache<list<ObjectBuilder>> $cache */
                $cache = new RuntimeCache();

                return new CacheObjectBuilderFactory($factory, $cache);
            },

            RecursiveCacheWarmupService::class => fn () => new RecursiveCacheWarmupService(
                $this->shared->get(TypeParser::class),
                $this->shared->get(CacheInterface::class),
                $this->get(ObjectImplementations::class),
                $this->shared->get(ClassDefinitionRepository::class),
                $this->get(ObjectBuilderFactory::class)
            ),
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
