<?php

declare(strict_types=1);

namespace CuyZ\Valinor;

use CuyZ\Valinor\Cache\Cache;
use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\ArgumentsMapper;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\TreeMapper;
use Throwable;

use function array_unique;
use function array_values;
use function is_callable;

/** @api */
final class MapperBuilder
{
    private Settings $settings;

    private Container $container;

    public function __construct()
    {
        $this->settings = new Settings();
    }

    /**
     * Allows the mapper to infer an implementation for a given interface.
     *
     * The callback can take any arguments, that will automatically be mapped
     * using the given source. These arguments can then be used to decide which
     * implementation should be used.
     *
     * The callback *must* be pure, its output must be deterministic:
     * {@see https://en.wikipedia.org/wiki/Pure_function}
     *
     * Example:
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->infer(UuidInterface::class, fn () => MyUuid::class)
     *     ->infer(SomeInterface::class, fn (string $type) => match($type) {
     *         'foo' => Foo::class,
     *         'bar' => Bar::class,
     *         default => throw new DomainException("Unhandled type `$type`.")
     *     })
     *     ->mapper()
     *     ->map(SomeInterface::class, [
     *         'type' => 'foo',
     *         'uuid' => 'a6868d61-acba-406d-bcff-30ecd8c0ceb6',
     *     ]);
     * ```
     *
     * @pure
     * @param interface-string|class-string $name
     * @param pure-callable $callback
     */
    public function infer(string $name, callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->inferredMapping[$name] = $callback;

        return $clone;
    }

    /**
     * Registers a constructor that can be used by the mapper to create an
     * instance of an object.
     *
     * Note that depending on your needs, a more straightforward way to register
     * a constructor is to use the following attribute on a static method:
     * {@see \CuyZ\Valinor\Mapper\Object\Constructor}
     *
     * A constructor is a callable that can be either:
     *
     * 1. A named constructor, also known as a static factory method
     * 2. The method of a service — for instance a repository
     * 3. A "callable object" — a class that declares an `__invoke` method
     * 4. Any other callable — including anonymous functions
     *
     * In any case, the return type of the callable will be resolved by the
     * mapper to know when to use it. Any argument can be provided and will
     * automatically be mapped using the given source. These arguments can then
     * be used to instantiate the object in the desired way.
     *
     * Registering any constructor will disable the native constructor — the
     * `__construct` method — of the targeted class. If for some reason it still
     * needs to be handled as well, the name of the class must be given to this
     * method.
     *
     * ```
     * final class SomeClass
     * {
     *     private string $foo;
     *
     *     private int $bar;
     *
     *     private array $otherClasses = [];
     *
     *     public function __construct(string $foo)
     *     {
     *         $this->foo = $foo;
     *     }
     *
     *     public static function namedConstructor(string $foo, int $bar): self
     *     {
     *         $instance = new self($foo);
     *         $instance->bar = $bar;
     *
     *         return $instance;
     *     }
     *
     *     public function addOtherClass(OtherClass $otherClass): void
     *     {
     *         $this->otherClasses[] = $otherClass;
     *     }
     * }
     *
     * final class SomeRepository
     * {
     *     public function findById(int $id): SomeClass
     *     {
     *         // …
     *     }
     * }
     *
     * final class SomeCallableObject
     * {
     *     public function __invoke(string $foo, int $bar, int $baz): SomeClass
     *     {
     *         // …
     *     }
     * }
     *
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->registerConstructor(
     *         // Named constructor
     *         SomeClass::namedConstructor(...),
     *
     *         // Method of an object
     *         (new SomeRepository())->findById(...),
     *
     *         // Callable object
     *         new SomeCallableObject(),
     *
     *         // Anonymous function
     *         function(string $string, OtherClass $otherClass): SomeClass {
     *             $someClass = new SomeClass($string);
     *             $someClass->addOtherClass($otherClass);
     *
     *             return $someClass;
     *         },
     *
     *         // Also allow the native constructor — the `__construct` method
     *         SomeClass::class,
     *     )
     *     ->mapper()
     *     ->map(SomeClass::class, [
     *         // …
     *     ]);
     * ```
     *
     * Enum constructors can be registered the same way:
     *
     * ```
     * enum SomeEnum: string
     * {
     *     case CASE_A = 'FOO_VALUE_1';
     *     case CASE_B = 'FOO_VALUE_2';
     *     case CASE_C = 'BAR_VALUE_1';
     *     case CASE_D = 'BAR_VALUE_2';
     *
     *     // @param 'FOO'|'BAR' $type
     *     // @param int<1, 2> $number
     *     public static function fromMatrix(string $type, int $number): self
     *     {
     *         return self::from("{$type}_VALUE_{$number}");
     *     }
     * }
     *
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->registerConstructor(
     *         // Allow the native constructor to be used
     *         SomeEnum::class,
     *
     *         // Register a named constructor
     *         SomeEnum::fromMatrix(...)
     *     )
     *     ->mapper()
     *     ->map(SomeEnum::class, [
     *         'type' => 'FOO',
     *         'number' => 'BAR',
     *     ]);
     * ```
     *
     * The constructor *must* be pure, its output must be deterministic:
     * {@see https://en.wikipedia.org/wiki/Pure_function}
     *
     * @pure
     * @param pure-callable|class-string ...$constructors
     */
    public function registerConstructor(callable|string ...$constructors): self
    {
        $clone = clone $this;

        foreach ($constructors as $constructor) {
            if (is_callable($constructor)) {
                $clone->settings->customConstructors[] = $constructor;
            } else {
                $clone->settings->nativeConstructors[$constructor] = null;
            }
        }

        return $clone;
    }

    /**
     * Describes which date formats will be supported during mapping.
     *
     * By default, the dates will accept any valid timestamp or RFC 3339-formatted
     * value.
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     // Both `Cookie` and `ATOM` formats will be accepted
     *     ->supportDateFormats(DATE_COOKIE, DATE_ATOM)
     *     ->mapper()
     *     ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
     * ```
     *
     * @pure
     * @param non-empty-string $format
     * @param non-empty-string ...$formats
     */
    public function supportDateFormats(string $format, string ...$formats): self
    {
        $clone = clone $this;
        $clone->settings->supportedDateFormats = array_values(array_unique([$format, ...$formats]));

        return $clone;
    }

    /**
     * Returns the date formats supported during mapping.
     *
     * By default, any valid timestamp or RFC 3339-formatted value are accepted.
     * Custom formats can be set using method `supportDateFormats()`.
     *
     * @pure
     * @return non-empty-array<non-empty-string>
     */
    public function supportedDateFormats(): array
    {
        return $this->settings->supportedDateFormats;
    }

    /**
     * Inject a cache implementation that will be in charge of caching heavy
     * data used by the mapper. It is *strongly* recommended to use it when the
     * application runs in a production environment.
     *
     * An implementation is provided out of the box, which writes cache entries
     * in the file system.
     *
     * When the application runs in a development environment, the cache
     * implementation should be decorated with `FileWatchingCache`. This service
     * will watch the files of the application and invalidate cache entries when
     * a PHP file is modified by a developer — preventing the library not
     * behaving as expected when the signature of a property or a method changes.
     *
     * ```
     * $cache = new \CuyZ\Valinor\Cache\FileSystemCache('path/to/cache-dir');
     *
     * if ($isApplicationInDevelopmentEnvironment) {
     *     $cache = new \CuyZ\Valinor\Cache\FileWatchingCache($cache);
     * }
     *
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->withCache($cache)
     *     ->mapper()
     *     ->map(SomeClass::class, [
     *         // …
     *     ]);
     * ```
     *
     * @pure
     */
    public function withCache(Cache $cache): self
    {
        $clone = clone $this;
        $clone->settings->cache = $cache;

        return $clone;
    }

    /**
     * With this setting enabled, scalar types will accept castable values:
     *
     * - Integer types will accept any valid numeric value, for instance the
     *   string value "42".
     *
     * - Float types will accept any valid numeric value, for instance the
     *   string value "1337.42".
     *
     * - String types will accept any integer, float or object implementing the
     *   `Stringable` interface.
     *
     * - Boolean types will accept any truthy or falsy value:
     *     - "true" (string), "1" (string) and 1 (int) will be cast to `true`
     *     - "false" (string), "0" (string) and 0 (int) will be cast to `false`
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->allowScalarValueCasting()
     *     ->mapper()
     *     ->map('array{id: string, price: float, active: bool}', [
     *         'id' => 549465210, // Will be cast to string
     *         'price' => '42.39', // Will be cast to float
     *         'active' => 1, // Will be cast to bool
     *     ]);
     * ```
     *
     * @pure
     */
    public function allowScalarValueCasting(): self
    {
        $clone = clone $this;
        $clone->settings->allowScalarValueCasting = true;

        return $clone;
    }

    /**
     * By default, list types will only accept sequential keys starting from 0.
     *
     * This setting allows the mapper to convert associative arrays to a list
     * with sequential keys.
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->allowNonSequentialList()
     *     ->mapper()
     *     ->map('list<int>', [
     *         'foo' => 42,
     *         'bar' => 1337,
     *     ]);
     *
     * // => [0 => 42, 1 => 1337]
     * ```
     *
     * @pure
     */
    public function allowNonSequentialList(): self
    {
        $clone = clone $this;
        $clone->settings->allowNonSequentialList = true;

        return $clone;
    }

    /**
     * Allows the mapper to accept undefined values (missing from the input), by
     * converting them to `null` (if the current type is nullable) or an empty
     * array (if the current type is an object or an iterable).
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->allowUndefinedValues()
     *     ->mapper()
     *     ->map('array{name: string, age: int|null}', [
     *         'name' => 'John Doe',
     *         // 'age' is not defined
     *     ]);
     *
     * // => ['name' => 'John Doe', 'age' => null]
     * ```
     *
     * @pure
     */
    public function allowUndefinedValues(): self
    {
        $clone = clone $this;
        $clone->settings->allowUndefinedValues = true;

        return $clone;
    }

    /**
     * By default, an error is raised when a source array contains keys that
     * do not match a class property/parameter or a shaped array element.
     *
     * This setting allows the mapper to ignore these superfluous keys.
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->allowSuperfluousKeys()
     *     ->mapper()
     *     ->map('array{name: string, age: int}', [
     *         'name' => 'John Doe',
     *         'age' => 42,
     *         'city' => 'Paris', // Will be ignored
     *     ]);
     * ```
     *
     * @pure
     */
    public function allowSuperfluousKeys(): self
    {
        $clone = clone $this;
        $clone->settings->allowSuperfluousKeys = true;

        return $clone;
    }

    /**
     * Allows permissive types `mixed` and `object` to be used during mapping.
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->allowPermissiveTypes()
     *     ->mapper()
     *     ->map('array{name: string, data: mixed}', [
     *         'name' => 'some_product',
     *         'data' => 42, // Could be any value
     *     ]);
     * ```
     *
     * @pure
     */
    public function allowPermissiveTypes(): self
    {
        $clone = clone $this;
        $clone->settings->allowPermissiveTypes = true;

        return $clone;
    }

    /**
     * A mapper converter allows users to hook into the mapping process and
     * apply custom logic to the input, by defining a callable signature that
     * properly describes when it should be called:
     *
     * - A first argument with a type matching the expected input being mapped
     * - A return type representing the targeted mapped type
     *
     * These two types are enough for the library to know when to call the
     * converter and can contain advanced type annotations for more specific
     * use cases.
     *
     * Below is a basic example of a converter that converts string inputs to
     * uppercase:
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->registerConverter(fn (string $value): string => strtoupper($value))
     *     ->mapper()
     *     ->map('string', 'hello world'); // 'HELLO WORLD'
     * ```
     *
     * Converters can be chained, allowing multiple transformations to be
     * applied to a value. A second `callable` parameter can be declared,
     * allowing the current converter to call the next one in the chain.
     *
     * A priority can be given to a converter to control the order in which
     * converters are applied. The higher the priority, the earlier the
     * converter will be executed. The default priority is 0.
     *
     * An attribute on a property or a class can act as a converter if:
     *  1. It defines a `map` method.
     *  2. It is registered using either the `registerConverter()` method or
     *     the following attribute: {@see \CuyZ\Valinor\Mapper\AsConverter}
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *
     *     // The type of the first parameter of the converter will determine
     *     // when it will be used by the mapper.
     *     ->registerConverter(
     *         fn (string $value, callable $next): string => $next(strtoupper($value))
     *     )
     *
     *     // Converters can be chained, the last registered one will take
     *     // precedence over the previous ones, which can be called using the
     *     // `$next` parameter.
     *     ->registerConverter(
     *         fn (string $value, callable $next): string => $next($value . '!')
     *     )
     *
     *     // A priority can be given to a converter, to make sure it is called
     *     // before or after another one.
     *     ->registerConverter(
     *         fn (string $value, callable $next): string => $next($value . '?'),
     *         priority: -100 // Negative priority: converter is called early
     *     )
     *
     *     // External converter attributes must be registered before they are
     *     // used by the mapper.
     *     ->registerConverter(\Some\External\ConverterAttribute::class)
     *
     *     ->mapper()
     *     ->map('string', 'hello world'); // 'HELLO WORLD!?'
     * ```
     *
     * It is also possible to register attributes that share a common interface
     * by giving the interface name to the registration method.
     *
     * ```
     * namespace My\App;
     *
     * interface MyAttributeInterface {}
     *
     * #[\Attribute]
     * final class SomeAttribute implements \My\App\MyAttributeInterface {}
     *
     * #[\Attribute]
     * final class SomeOtherAttribute implements \My\App\MyAttributeInterface {}
     *
     * (new \CuyZ\Valinor\MapperBuilder())
     *     // Registers both `SomeAttribute` and `SomeOtherAttribute` attributes
     *     ->registerConverter(\My\App\MyAttributeInterface::class)
     *     ->mapper()
     *     ->map(…);
     * ```
     *
     * The converter *must* be pure, its output must be deterministic:
     * {@see https://en.wikipedia.org/wiki/Pure_function}
     *
     * @pure
     * @param pure-callable|class-string $converter
     */
    public function registerConverter(callable|string $converter, int $priority = 0): self
    {
        $clone = clone $this;

        if (is_callable($converter)) {
            $clone->settings->mapperConverters[$priority][] = $converter;
        } else {
            $clone->settings->mapperConverterAttributes[$converter] = null;
        }

        return $clone;
    }

    /**
     * Filters which userland exceptions are allowed during the mapping.
     *
     * It is advised to use this feature with caution: userland exceptions may
     * contain sensible information — for instance an SQL exception showing a
     * part of a query should never be allowed. Therefore, only an exhaustive
     * list of carefully chosen exceptions should be filtered.
     *
     * ```
     * final class SomeClass
     * {
     *     public function __construct(string $value)
     *     {
     *         \Webmozart\Assert\Assert::startsWith($value, 'foo_');
     *     }
     * }
     *
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->filterExceptions(function (Throwable $exception) {
     *         if ($exception instanceof \Webmozart\Assert\InvalidArgumentException) {
     *             return \CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder::from($exception);
     *         }
     *
     *         // If the exception should not be caught by this library, it must
     *         // be thrown again.
     *         throw $exception;
     *     })
     *     ->mapper()
     *     ->map(SomeClass::class, [
     *         // …
     *     ]);
     * ```
     *
     * @pure
     * @param callable(Throwable): ErrorMessage $filter
     */
    public function filterExceptions(callable $filter): self
    {
        $clone = clone $this;
        $clone->settings->exceptionFilter = $filter;

        return $clone;
    }

    /**
     * Warms up the injected cache implementation with the provided type
     * signatures. This will improve the performance when the first call to the
     * mapper is done for each of these types.
     *
     * ```
     * $mapperBuilder = (new \CuyZ\Valinor\MapperBuilder())
     *    ->withCache(new \CuyZ\Valinor\Cache\FileSystemCache('path/to/dir'));
     *
     * // During the build:
     * $mapperBuilder->warmupCacheFor(
     *        // This will also recursively warm up the cache for the types of
     *        // the class properties.
     *        SomeClass::class,
     *
     *        // Any valid type signature can be used.
     *        'non-empty-list<string, SomeClass>',
     *        'array{name: string, age: int}',
     *    );
     *
     * // In the application:
     * $mapperBuilder->mapper()->map(SomeClass::class, […]);
     * ```
     */
    public function warmupCacheFor(string ...$signatures): void
    {
        if (! isset($this->settings->cache)) {
            return;
        }

        $this->container()->cacheWarmupService()->warmup(...$signatures);
    }

    /**
     * Clears all persisted cache entries from the registered cache
     * implementation.
     */
    public function clearCache(): void
    {
        if (! isset($this->settings->cache)) {
            return;
        }

        $this->settings->cache->clear();
    }

    /** @pure */
    public function mapper(): TreeMapper
    {
        return $this->container()->treeMapper();
    }

    /** @pure */
    public function argumentsMapper(): ArgumentsMapper
    {
        return $this->container()->argumentsMapper();
    }

    public function __clone()
    {
        $this->settings = clone $this->settings;
        unset($this->container);
    }

    private function container(): Container
    {
        return ($this->container ??= new Container($this->settings));
    }
}
