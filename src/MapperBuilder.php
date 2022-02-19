<?php

declare(strict_types=1);

namespace CuyZ\Valinor;

use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\TreeMapper;

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
     * @param interface-string $interfaceName
     */
    public function infer(string $interfaceName, callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->interfaceMapping[$interfaceName] = $callback;

        return $clone;
    }

    /**
     * Defines a custom way to build an object during the mapping.
     *
     * The return type of the callback will be resolved by the mapping to know
     * when to use it.
     *
     * The callback can take any arguments, that will automatically be mapped
     * using the given source. These arguments can then be used to instantiate
     * the object in the desired way.
     *
     * Example:
     *
     * ```
     * (new \CuyZ\Valinor\MapperBuilder())
     *     ->bind(function(string $string, OtherClass $otherClass): SomeClass {
     *         $someClass = new SomeClass($string);
     *         $someClass->addOtherClass($otherClass);
     *
     *         return $someClass;
     *     })
     *     ->mapper()
     *     ->map(SomeClass::class, [
     *         // …
     *     ]);
     * ```
     */
    public function bind(callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->objectBinding[] = $callback;

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
