<?php

declare(strict_types=1);

namespace CuyZ\Valinor;

use CuyZ\Valinor\Cache\Cache;
use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Normalizer\Normalizer;

use function is_callable;

/** @api */
final class NormalizerBuilder
{
    private Settings $settings;

    private Container $container;

    public function __construct()
    {
        $this->settings = new Settings();
    }

    /**
     * Inject a cache implementation that will be in charge of caching heavy
     * data used by the normalizer. It is *strongly* recommended to use it when
     * the application runs in a production environment.
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
     * (new \CuyZ\Valinor\NormalizerBuilder())
     *     ->withCache($cache)
     *     ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
     *     ->normalize($someData);
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
     *     the attribute: {@see \CuyZ\Valinor\Normalizer\AsTransformer}
     *
     * Example:
     *
     * ```
     * (new \CuyZ\Valinor\NormalizerBuilder())
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
     *     ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
     *     ->normalize('Hello world'); // HELLO WORLD?!
     * ```
     *
     * The transformer *must* be pure, its output must be deterministic:
     * {@see https://en.wikipedia.org/wiki/Pure_function}
     *
     * @pure
     * @param pure-callable|class-string $transformer
     */
    public function registerTransformer(callable|string $transformer, int $priority = 0): self
    {
        $clone = clone $this;

        if (is_callable($transformer)) {
            $clone->settings->normalizerTransformers[$priority][] = $transformer;
        } else {
            $clone->settings->normalizerTransformerAttributes[$transformer] = null;
        }

        return $clone;
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

    /**
     * @pure
     *
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
