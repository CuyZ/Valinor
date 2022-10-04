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
use CuyZ\Valinor\Definition\Repository\Reflection\CombinedAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\DoctrineAnnotationsRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\NativeAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionFunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\AttributeObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\CacheObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\CollisionObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\ConstructorObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\DateTimeObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\ReflectionObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\StrictTypesObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ArrayNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\CasterNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\CasterProxyNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ClassNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ErrorCatcherNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\InterfaceNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\IterableNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ListNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\NodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ObjectImplementations;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ScalarNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ShapedArrayNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ShellVisitorNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\StrictNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\UnionNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ValueAlteringNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Visitor\AttributeShellVisitor;
use CuyZ\Valinor\Mapper\Tree\Visitor\ShellVisitor;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Mapper\TreeMapperContainer;
use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\HandleClassGenericSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\Template\BasicTemplateParser;
use CuyZ\Valinor\Type\Parser\Template\TemplateParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Resolver\Union\UnionNullNarrower;
use CuyZ\Valinor\Type\Resolver\Union\UnionScalarNarrower;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use Psr\SimpleCache\CacheInterface;

use function call_user_func;

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
            TreeMapper::class => fn () => new TreeMapperContainer(
                $this->get(TypeParser::class),
                new RootNodeBuilder($this->get(NodeBuilder::class))
            ),

            ShellVisitor::class => fn () => new AttributeShellVisitor(),

            NodeBuilder::class => function () use ($settings) {
                $listNodeBuilder = new ListNodeBuilder($settings->flexible);
                $arrayNodeBuilder = new ArrayNodeBuilder($settings->flexible);

                $builder = new CasterNodeBuilder([
                    ListType::class => $listNodeBuilder,
                    NonEmptyListType::class => $listNodeBuilder,
                    ArrayType::class => $arrayNodeBuilder,
                    NonEmptyArrayType::class => $arrayNodeBuilder,
                    IterableType::class => $arrayNodeBuilder,
                    ShapedArrayType::class => new ShapedArrayNodeBuilder($settings->flexible),
                    ScalarType::class => new ScalarNodeBuilder($settings->flexible),
                ]);

                $builder = new UnionNodeBuilder($builder, new UnionNullNarrower(new UnionScalarNarrower()));

                $builder = new ClassNodeBuilder(
                    $builder,
                    $this->get(ClassDefinitionRepository::class),
                    $this->get(ObjectBuilderFactory::class),
                    $settings->flexible
                );

                $builder = new InterfaceNodeBuilder(
                    $builder,
                    $this->get(ObjectImplementations::class),
                    $this->get(ClassDefinitionRepository::class),
                    $this->get(ObjectBuilderFactory::class),
                    $settings->flexible
                );

                $builder = new CasterProxyNodeBuilder($builder);
                $builder = new IterableNodeBuilder($builder);
                $builder = new ValueAlteringNodeBuilder(
                    $builder,
                    new FunctionsContainer(
                        $this->get(FunctionDefinitionRepository::class),
                        $settings->valueModifier
                    )
                );
                $builder = new StrictNodeBuilder($builder, $settings->flexible);
                $builder = new ShellVisitorNodeBuilder($builder, $this->get(ShellVisitor::class));

                return new ErrorCatcherNodeBuilder($builder, $settings->exceptionFilter);
            },

            ObjectImplementations::class => fn () => new ObjectImplementations(
                new FunctionsContainer(
                    $this->get(FunctionDefinitionRepository::class),
                    $settings->interfaceMapping
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
                $factory = new DateTimeObjectBuilderFactory($factory, $this->get(FunctionDefinitionRepository::class));
                $factory = new AttributeObjectBuilderFactory($factory);
                $factory = new CollisionObjectBuilderFactory($factory);

                if (! $settings->flexible) {
                    $factory = new StrictTypesObjectBuilderFactory($factory);
                }

                /** @var RuntimeCache<list<ObjectBuilder>> $cache */
                $cache = new RuntimeCache();

                return new CacheObjectBuilderFactory($factory, $cache);
            },

            ClassDefinitionRepository::class => fn () => new CacheClassDefinitionRepository(
                new ReflectionClassDefinitionRepository(
                    $this->get(TypeParserFactory::class),
                    $this->get(AttributesRepository::class),
                ),
                $this->get(CacheInterface::class)
            ),

            FunctionDefinitionRepository::class => fn () => new CacheFunctionDefinitionRepository(
                new ReflectionFunctionDefinitionRepository(
                    $this->get(TypeParserFactory::class),
                    $this->get(AttributesRepository::class),
                ),
                $this->get(CacheInterface::class)
            ),

            AttributesRepository::class => function () use ($settings) {
                if (! $settings->enableLegacyDoctrineAnnotations) {
                    return new NativeAttributesRepository();
                }

                /** @infection-ignore-all */
                if (PHP_VERSION_ID >= 8_00_00) {
                    return new CombinedAttributesRepository();
                }

                /** @infection-ignore-all */
                // @codeCoverageIgnoreStart
                return new DoctrineAnnotationsRepository(); // @codeCoverageIgnoreEnd
            },

            TypeParserFactory::class => fn () => new LexingTypeParserFactory(
                $this->get(TemplateParser::class)
            ),

            TypeParser::class => function () {
                $factory = $this->get(TypeParserFactory::class);
                $parser = $factory->get(new HandleClassGenericSpecification());

                return new CachedParser($parser);
            },

            TemplateParser::class => fn () => new BasicTemplateParser(),

            RecursiveCacheWarmupService::class => fn () => new RecursiveCacheWarmupService(
                $this->get(TypeParser::class),
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
