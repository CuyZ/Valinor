<?php

declare(strict_types=1);

namespace CuyZ\Valinor;

use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\ArgumentsMapper;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Normalizer\Normalizer;
use Psr\SimpleCache\CacheInterface;
use Throwable;

use function array_unique;
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
     * The callback *must* be pure, its output must be deterministic.
     * @see https://en.wikipedia.org/wiki/Pure_function
     *
     * Example:
     *
     * ```php
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
     * @param interface-string|class-string $name
     * @psalm-param pure-callable $callback
     */
    public function infer(string $name, callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->inferredMapping[$name] = $callback;

        return $clone;
    }

    /**
     * Registers a constructor that can be used by the mapper to create an
     * instance of an object. A constructor is a callable that can be either:
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
     * A constructor *must* be pure, its output must be deterministic.
     * @see https://en.wikipedia.org/wiki/Pure_function
     *
     * ```php
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
     *         // …or for PHP < 8.1:
     *         [SomeClass::class, 'namedConstructor'],
     *
     *         // Method of an object
     *         (new SomeRepository())->findById(...),
     *         // …or for PHP < 8.1:
     *         [new SomeRepository(), 'findById'],
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
     * * ```php
     * enum SomeEnum: string
     * {
     *     case CASE_A = 'FOO_VALUE_1';
     *     case CASE_B = 'FOO_VALUE_2';
     *     case CASE_C = 'BAR_VALUE_1';
     *     case CASE_D = 'BAR_VALUE_2';
     *
     *     /**
     *      * \@param 'FOO'|'BAR' $type
     *      * \@param int<1, 2> $number
     *      * /
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
     * @psalm-param pure-callable|class-string ...$constructors
     * @param callable|class-string ...$constructors
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
     * ```php
     * (new \CuyZ\Valinor\MapperBuilder())
     *     // Both `Cookie` and `ATOM` formats will be accepted
     *     ->supportDateFormats(DATE_COOKIE, DATE_ATOM)
     *     ->mapper()
     *     ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
     * ```
     *
     * @param non-empty-string $format
     * @param non-empty-string ...$formats
     */
    public function supportDateFormats(string $format, string ...$formats): self
    {
        $clone = clone $this;
        $clone->settings->supportedDateFormats = array_unique([$format, ...$formats]);

        return $clone;
    }

    /**
     * Returns the date formats supported during mapping.
     *
     * By default, any valid timestamp or RFC 3339-formatted value are accepted.
     * Custom formats can be set using method `supportDateFormats()`.
     *
     * @return non-empty-array<non-empty-string>
     */
    public function supportedDateFormats(): array
    {
        return $this->settings->supportedDateFormats;
    }

    /**
     * Inject a cache implementation that will be in charge of caching heavy
     * data used by the mapper.
     *
     * An implementation is provided by the library, which writes cache entries
     * in the file system; it is strongly recommended to use it when the
     * application runs in production environment.
     *
     * It is also possible to use any PSR-16 compliant implementation, as long
     * as it is capable of caching the entries handled by the library.
     *
     * When the application runs in a development environment, the cache
     * implementation should be decorated with `FileWatchingCache`, which will
     * watch the files of the application and invalidate cache entries when a
     * PHP file is modified by a developer — preventing the library not behaving
     * as expected when the signature of a property or a method changes.
     *
     * ```php
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
     */
    public function withCache(CacheInterface $cache): self
    {
        $clone = clone $this;
        $clone->settings->cache = $cache;

        return $clone;
    }

    /**
     * @template T
     * @psalm-param pure-callable(T): T $callback
     * @param callable(T): T $callback
     */
    public function alter(callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->valueModifier[] = $callback;

        return $clone;
    }

    /**
     * Changes the behaviours explained below:
     *
     * ```php
     * $flexibleMapper = (new \CuyZ\Valinor\MapperBuilder())
     *     ->enableFlexibleCasting()
     *     ->mapper();
     *
     * // ---
     * // Scalar types will accept non-strict values; for instance an integer
     * // type will accept any valid numeric value like the *string* "42".
     *
     * $flexibleMapper->map('int', '42');
     * // => 42
     *
     * // ---
     * // List type will accept non-incremental keys.
     *
     * $flexibleMapper->map('list<int>', ['foo' => 42, 'bar' => 1337]);
     * // => [0 => 42, 1 => 1338]
     *
     * // ---
     * // If a value is missing in a source for a node that accepts `null`, the
     * // node will be filled with `null`.
     *
     * $flexibleMapper->map(
     *     'array{foo: string, bar: null|string}',
     *     ['foo' => 'foo'] // `bar` is missing
     * );
     * // => ['foo' => 'foo', 'bar' => null]
     *
     * // ---
     * // Array and list types will convert `null` or missing values to an empty
     * // array.
     *
     * $flexibleMapper->map(
     *     'array{foo: string, bar: array<string>}',
     *     ['foo' => 'foo'] // `bar` is missing
     * );
     * // => ['foo' => 'foo', 'bar' => []]
     * ```
     */
    public function enableFlexibleCasting(): self
    {
        $clone = clone $this;
        $clone->settings->enableFlexibleCasting = true;

        return $clone;
    }

    /**
     * Superfluous keys in source arrays will be ignored, preventing errors when
     * a value is not bound to any object property/parameter or shaped array
     * element.
     *
     * ```php
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->allowSuperfluousKeys()
     *     ->mapper()
     *     ->map(
     *         'array{foo: string, bar: int}',
     *         [
     *             'foo' => 'foo',
     *             'bar' => 42,
     *             'baz' => 1337.404, // `baz` will be ignored
     *         ]
     *     );
     * ```
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
     * ```php
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->allowPermissiveTypes()
     *     ->mapper()
     *     ->map(
     *         'array{foo: string, bar: mixed}',
     *         [
     *             'foo' => 'foo',
     *             'bar' => 42, // Could be any value
     *         ]
     *     );
     * ```
     */
    public function allowPermissiveTypes(): self
    {
        $clone = clone $this;
        $clone->settings->allowPermissiveTypes = true;

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
     * The filter callback *must* be pure, its output must be deterministic.
     * @see https://en.wikipedia.org/wiki/Pure_function
     *
     * ```php
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
     * @psalm-param pure-callable(Throwable): ErrorMessage $filter
     * @param callable(Throwable): ErrorMessage $filter
     */
    public function filterExceptions(callable $filter): self
    {
        $clone = clone $this;
        $clone->settings->exceptionFilter = $filter;

        return $clone;
    }

    /**
     * A transformer is responsible for transforming specific values during a
     * normalization process.
     *
     * Transformers can be chained, the last registered one will take precedence
     * over the previous ones.
     *
     * By specifying the type of its first parameter, the given callable will
     * determine when the transformer is used. Advanced type annotations like
     * `non-empty-string` can be used to target a more specific type.
     *
     * A second `callable` parameter may be declared, allowing to call the next
     * transformer in the chain and get the modified value from it, before
     * applying its own transformations.
     *
     * A priority can be given to a transformer, to make sure it is called
     * before or after another one. The higher the priority, the sooner the
     * transformer will be called. Default priority is 0.
     *
     * An attribute on a property or a class can act as a transformer if:
     *  1. It defines a `normalize` or `normalizeKey` method.
     *  2. It is registered using either the `registerTransformer()` method or
     *     the following attribute: @see \CuyZ\Valinor\Normalizer\AsTransformer
     *
     * Example:
     *
     * ```php
     * (new \CuyZ\Valinor\MapperBuilder())
     *
     *     // The type of the first parameter of the transformer will determine
     *     // when it will be used by the normalizer.
     *     ->registerTransformer(
     *         fn (string $value, callable $next) => strtoupper($next())
     *     )
     *
     *     // Transformers can be chained, the last registered one will take
     *     // precedence over the previous ones, which can be called using the
     *     // `$next` parameter.
     *     ->registerTransformer(
     *         fn (string $value, callable $next) => $next() . '!'
     *     )
     *
     *     // A priority can be given to a transformer, to make sure it is
     *     // called before or after another one.
     *     ->registerTransformer(
     *         fn (string $value, callable $next) => $next() . '?',
     *         priority: -100 // Negative priority: transformer is called early
     *     )
     *
     *     // External transformer attributes must be registered before they are
     *     // used by the normalizer.
     *     ->registerTransformer(\Some\External\TransformerAttribute::class)
     *
     *     ->normalizer()
     *     ->normalize('Hello world'); // HELLO WORLD?!
     * ```
     *
     * @psalm-param pure-callable|class-string $transformer
     * @param callable|class-string $transformer
     */
    public function registerTransformer(callable|string $transformer, int $priority = 0): self
    {
        $clone = clone $this;

        if (is_callable($transformer)) {
            $clone->settings->transformers[$priority][] = $transformer;
        } else {
            $clone->settings->transformerAttributes[$transformer] = null;
        }

        return $clone;
    }

    /**
     * Warms up the injected cache implementation with the provided class names.
     *
     * By passing a class which contains recursive objects, every nested object
     * will be cached as well.
     */
    public function warmup(string ...$signatures): void
    {
        $this->container()->cacheWarmupService()->warmup(...$signatures);
    }

    public function mapper(): TreeMapper
    {
        return $this->container()->treeMapper();
    }

    public function argumentsMapper(): ArgumentsMapper
    {
        return $this->container()->argumentsMapper();
    }

    /**
     * @template T of Normalizer
     *
     * @param Format<T> $format
     * @return T
     */
    public function normalizer(Format $format): Normalizer
    {
        return $this->container()->normalizer($format);
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
