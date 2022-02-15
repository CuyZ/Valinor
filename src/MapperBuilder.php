<?php

declare(strict_types=1);

namespace CuyZ\Valinor;

use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use LogicException;

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
     * @param class-string $interfaceName
     * @param callable(Shell): class-string $callback
     */
    public function infer(string $interfaceName, callable $callback): self
    {
        $clone = clone $this;
        $clone->settings->interfaceMapping[$interfaceName] = $callback;

        return $clone;
    }

    /**
     * @param callable(mixed): object $callback
     */
    public function bind(callable $callback): self
    {
        $reflection = Reflection::ofCallable($callback);

        $nativeType = $reflection->getReturnType();
        $typeFromDocBlock = Reflection::docBlockReturnType($reflection);

        if ($typeFromDocBlock) {
            $type = $typeFromDocBlock;
        } elseif ($nativeType) {
            $type = Reflection::flattenType($nativeType);
        } else {
            throw new LogicException('No return type was found for this callable.');
        }

        $clone = clone $this;
        $clone->settings->objectBinding[$type] = $callback;

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
