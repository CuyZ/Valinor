# Application and framework integration

This library is framework-agnostic, but using it in an application that relies
on a framework is still possible.

For Symfony applications, check out the [chapter below](#symfony-bundle). For
other frameworks, check out the [custom integration
chapter](#custom-integration).

## Symfony bundle

A bundle is available to automatically integrate this library into a Symfony
application.

```bash
composer require cuyz/valinor-bundle
```

The documentation of this bundle can be found
[on the GitHub repository](https://github.com/CuyZ/Valinor-Bundle/#readme).

## Custom integration

If the application does not have a dedicated framework integration, it is still
possible to integrate this library manually.

### Mapper and normalizer registration

The most important task of the integration is to correctly register the
mapper(s) and normalizer(s) used in the application. Mapper instance(s) should
be shared between services whenever possible; this is important because heavy
operations are cached internally to improve performance during runtime.

If the framework uses a service container, it should be configured in a way
where the mapper(s) and normalizer(s) are registered as shared services. In
other cases, direct instantiation of these services should be avoided.

```php
$mapperBuilder = new \CuyZ\Valinor\MapperBuilder();
$normalizerBuilder = new \CuyZ\Valinor\NormalizerBuilder();

// …customization of the mapper builder and normalization builder…

$mapper = $mapperBuilder->mapper();
$jsonNormalizer = $normalizerBuilder->normalizer(\CuyZ\Valinor\Normalizer\Format::json());

$container->addSharedService('mapper', $mapper);
$container->addSharedService('normalizer_json', $jsonNormalizer);
```

### Registering a cache

As mentioned above, caching is important to allow the mapper to perform well.
The application really should provide a cache implementation to the mapper
builder.

As stated in the [performance chapter], the library provides a cache
implementation out of the box which can be used in any application.

```php
$cache = new \CuyZ\Valinor\Cache\FileSystemCache('path/to/cache-directory');

// If the application can detect when it is in development environment, it is
// advised to wrap the cache with a `FileWatchingCache` instance, to avoid
// having to manually clear the cache when a file changes during development.
if ($isApplicationInDevelopmentEnvironment) {
    $cache = new \CuyZ\Valinor\Cache\FileWatchingCache($cache);
}

$mapperBuilder = $mapperBuilder->withCache($cache);
$normalizerBuilder = $normalizerBuilder->withCache($cache);
```

### Warming up and clearing the cache

The cache can be warmed up to ease the application cold start. If the framework
has a way to automatically detect which classes will be used by the mapper, they
should be given to the `warmupCacheFor` method, as stated in the [cache warmup
chapter].

The cache can also be cleared when needed, this can be done by calling
`MapperBuilder::clearCache()` or `NormalizerBuilder::clearCache()`.

### Other configurations

Concerning other configurations, such as [flexible modes], [configuring
supported date formats] or [registering custom constructors], an integration 
should be provided to configure the mapper/normalizer builder in a convenient
way — how it is done will mostly depend on the framework features and its main
philosophy.

[performance chapter]: performance-and-caching.md
[cache warmup chapter]: performance-and-caching.md#warming-up-cache
[flexible modes]: ../usage/type-strictness-and-flexibility.md
[configuring supported date formats]: ../how-to/deal-with-dates.md
[registering custom constructors]: ../how-to/use-custom-object-constructors.md
