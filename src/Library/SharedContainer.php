<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use CuyZ\Valinor\Cache\ChainCache;
use CuyZ\Valinor\Cache\KeySanitizerCache;
use CuyZ\Valinor\Cache\RuntimeCache;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\Cache\CacheClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Cache\CacheFunctionDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\NativeAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionFunctionDefinitionRepository;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\TypeParser;
use Psr\SimpleCache\CacheInterface;

use function spl_object_hash;

/** @internal */
final class SharedContainer
{
    /** @var array<string, self> */
    private static array $instances = [];

    /** @var array<class-string, object> */
    private array $services = [];

    /** @var array<class-string, callable(): object> */
    private array $factories;

    /**
     * @param CacheInterface<mixed>|null $cache
     */
    private function __construct(?CacheInterface $cache)
    {
        $this->factories = [
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

            AttributesRepository::class => fn () => new NativeAttributesRepository(),

            TypeParserFactory::class => fn () => new LexingTypeParserFactory(),

            TypeParser::class => fn () => $this->get(TypeParserFactory::class)->get(),

            CacheInterface::class => function () use ($cache) {
                $cacheWrapper = new RuntimeCache();

                if ($cache) {
                    $cacheWrapper = new ChainCache($cacheWrapper, new KeySanitizerCache($cache));
                }

                return $cacheWrapper;
            },
        ];
    }

    /**
     * @param CacheInterface<mixed>|null $cache
     */
    public static function new(?CacheInterface $cache): self
    {
        $key = $cache ? spl_object_hash($cache) : 'default';

        return self::$instances[$key] ??= new self($cache);
    }

    /**
     * @template T of object
     * @param class-string<T> $name
     * @return T
     */
    public function get(string $name): object
    {
        return $this->services[$name] ??= call_user_func($this->factories[$name]); // @phpstan-ignore-line
    }
}
