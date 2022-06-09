# Performance & caching

This library needs to parse a lot of information in order to handle all provided
features. Therefore, it is strongly advised to activate the cache to reduce
heavy workload between runtimes, especially when the application runs in a
production environment.

The library provides a cache implementation out of the box, which saves
cache entries into the file system.

!!! Note

    It is also possible to use any PSR-16 compliant implementation, as
    long as it is capable of caching the entries handled by the library.

When the application runs in a development environment, the cache implementation
should be decorated with `FileWatchingCache`, which will watch the files of the
application and invalidate cache entries when a PHP file is modified by a
developer — preventing the library not behaving as expected when the signature
of a property or a method changes.

```php
$cache = new \CuyZ\Valinor\Cache\FileSystemCache('path/to/cache-directory');

if ($isApplicationInDevelopmentEnvironment) {
    $cache = new \CuyZ\Valinor\Cache\FileWatchingCache($cache);
}

(new \CuyZ\Valinor\MapperBuilder())
    ->withCache($cache)
    ->mapper()
    ->map(SomeClass::class, [/* … */]);
```

## Warming up cache

The cache can be warmed up, for instance in a pipeline during the build and
deployment of the application.

!!! note

    The cache has to be registered first, otherwise the warmup will end
    up being useless.

```php
$cache = new \CuyZ\Valinor\Cache\FileSystemCache('path/to/cache-dir');

$mapperBuilder = (new \CuyZ\Valinor\MapperBuilder())->withCache($cache);

// During the build:
$mapperBuilder->warmup(SomeClass::class, SomeOtherClass::class);

// In the application:
$mapper->mapper()->map(SomeClass::class, [/* … */]);
```
