<?php

declare(strict_types=1);

namespace CuyZ\Valinor;

use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\TreeMapper;

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
     * @param interface-string $interfaceName
     */
    public function infer(string $interfaceName, callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->interfaceMapping[$interfaceName] = $callback;

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
     * @param callable|class-string ...$constructors
     */
    public function registerConstructor(...$constructors): self
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
     * @template T
     * @param callable(T): T $callback
     */
    public function alter(callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->valueModifier[] = $callback;

        return $clone;
    }

    /**
     * @param callable(Node): void $callback
     */
    public function visit(callable $callback): self
    {
        $this->settings->nodeVisitors[] = $callback;

        return $this;
    }

    public function withCacheDir(string $cacheDir): self
    {
        $clone = clone $this;
        $clone->settings->cacheDir = $cacheDir;

        return $clone;
    }

    public function withDisabledCacheSourceValidation(): self
    {
        $clone = clone $this;
        $clone->settings->validateCacheSource = false;

        return $clone;
    }

    /**
     * @deprecated It is not advised to use DoctrineAnnotation when using
     *             PHP >= 8, you should use built-in PHP attributes instead.
     */
    public function enableLegacyDoctrineAnnotations(): self
    {
        $clone = clone $this;
        $clone->settings->enableLegacyDoctrineAnnotations = true;

        return $clone;
    }

    /**
     * @deprecated use method `registerConstructor` instead.
     */
    public function bind(callable $callback): self
    {
        return $this->registerConstructor($callback);
    }

    public function mapper(): TreeMapper
    {
        return ($this->container ??= new Container($this->settings))->treeMapper();
    }

    public function __clone()
    {
        $this->settings = clone $this->settings;
        unset($this->container);
    }
}
