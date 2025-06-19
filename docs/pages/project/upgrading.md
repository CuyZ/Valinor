# Upgrading Valinor

## Upgrade from 1.x to 2.x

### Changes to messages/errors handling

Some changes have been made to the way messages and errors are handled.

It is now easier to fetch messages when error(s) occur during mapping:

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())->mapper()->map(/* … */);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Before (1.x):
    $messages = \CuyZ\Valinor\Mapper\Tree\Message\Messages::flattenFromNode(
        $error->node()
    );
    
    // After (2.x):
    $messages = $error->messages();
}
```

Some methods to access information from the message have been moved:

```php
// Before (1.x):
$name = $message->node()->name();
$path = $message->node()->path();
$type = $message->node()->type();
$type = $message->node()->sourceValue();

// After (2.x):
$name = $message->name();
$path = $message->path();
$type = $message->type();
$type = $message->sourceValue();
```

### Changes to messages codes

Codes have been changed for all messages that can be thrown during mapping. They
now are way more expressive. Here is the exhaustive list of changes and their
replacements:

- `1642183169` → `cannot_find_object_builder`
- `1632903281` → `invalid_source`
- `1607027306` → `cannot_resolve_type_from_union`
- `1654273010` → `invalid_list_key`
- `1630678334` → `invalid_value`
- `1630946163` → `invalid_array_key`
- `1655449641` → `missing_value`
- `1736015505` → `value_is_empty_array`
- `1736015714` → `value_is_empty_list`
- `1710263908` → `value_is_not_null`
- `1618739163` → `value_is_not_iterable`
- `1710262975` → `too_many_resolved_types_from_union`
- `1655117782` → `unexpected_keys`
- `1630686564` → `cannot_parse_datetime_format`

Note that a lot of error messages related to invalid scalar values mapping now
have a specific code, where it used to be `unknown`.

### Introduced `NormalizerBuilder` as the main entry for normalizers

The `NormalizerBuilder` class has been introduced and will now be the main entry
to instantiate normalizers. Therefore, the methods in `MapperBuilder` that used
to configure and return normalizers have been removed.

This decision aims to make a clear distinction between the mapper and the
normalizer configuration API, where confusion could arise when using both
services.

The `NormalizerBuilder` can be used like this:

```php
$normalizer = (new \CuyZ\Valinor\NormalizerBuilder())
    ->registerTransformer(
        fn (\DateTimeInterface $date) => $date->format('Y/m/d')
    )
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize($someData);
```

### Removed Simple Cache (PSR-16) handling

The Simple Cache PSR requirement has been removed from the library. This means
that external PSR implementations can no longer be used with this library.

This decision was taken because entries that were cached were always generated
PHP code, which is not what PSR is meant for. This led to confusion and
incorrect behavior when using the library with a custom implementation.

The library now uses its own cache interface, but the provided implementations
did not change, thus the upgrade should be pretty straightforward.

As a reminder, this is how cache should be injected in your applications:

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

Also note that this change led to a refactor that should improve confidence for
the file watching feature: before, the cache entries were sometimes not
invalidated properly when files changed during development. This should now be
better.

### Removed `MapperBuilder::enableFlexibleCasting()`

This method is removed in favor of three distinct modes:

- [`MapperBuilder::allowScalarValueCasting()`](../usage/type-strictness-and-flexibility.md#allowing-scalar-value-casting)
- [`MapperBuilder::allowNonSequentialList()`](../usage/type-strictness-and-flexibility.md#allowing-non-sequential-lists)
- [`MapperBuilder::allowUndefinedValues()`](../usage/type-strictness-and-flexibility.md#allowing-undefined-values)

The methods above should be used to fit more specific use cases.

### Removed `MapperBuilder::alter()`

This feature has been removed in favor of [mapper converters] which are more
flexible and powerful.

Note that the `MapperBuilder::alter()` was never really documented, so there are
good chances that only a few users were using it.

[mapper converters]: ../how-to/convert-input.md

### Removed classes and interfaces

The following classes/interfaces were part of the initial release but were
unused by the library. Although no one should have used them, they were part of
the public API so removing them is a breaking change.

- `\CuyZ\Valinor\Mapper\Source\IdentifiableSource`
- `\CuyZ\Valinor\Utility\Priority\HasPriority`
- `\CuyZ\Valinor\Utility\Priority\PrioritizedList`

### Class constructors marked as `@internal`

Some constructors have been marked as `@internal`, to ease future maintenance by
allowing changes to these methods without breaking the public API.

This change should not affect users as they should not be directly using these
anyway.

List of affected constructors:

- `\CuyZ\Valinor\Mapper\Tree\Message\Messages::__construct`
- `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::__construct`
- `\CuyZ\Valinor\Normalizer\ArrayNormalizer::__construct`
- `\CuyZ\Valinor\Normalizer\JsonNormalizer::__construct`
- `\CuyZ\Valinor\Normalizer\StreamNormalizer::__construct`

### Full list of breaking changes

- Removed `\Psr\SimpleCache\CacheInterface` dependency
- Removed `\CuyZ\Valinor\MapperBuilder::alter()`
    * Use `\CuyZ\Valinor\MapperBuilder::registerConverter` instead 
- Removed `\CuyZ\Valinor\MapperBuilder::enableFlexibleCasting()`
    * Use the following methods instead:
        - `\CuyZ\Valinor\MapperBuilder::allowScalarValueCasting()`
        - `\CuyZ\Valinor\MapperBuilder::allowNonSequentialList()`
        - `\CuyZ\Valinor\MapperBuilder::allowUndefinedValues()`
- Removed `\CuyZ\Valinor\MapperBuilder::registerTransformer()`
    * Use `\CuyZ\Valinor\NormalizerBuilder` instead
- Removed `\CuyZ\Valinor\MapperBuilder::normalizer()`
    * Use `\CuyZ\Valinor\NormalizerBuilder` instead
- Renamed `\CuyZ\Valinor\MapperBuilder::warmup()` to
  `\CuyZ\Valinor\MapperBuilder::warmupCacheFor()`
- Removed `\CuyZ\Valinor\Mapper\MappingError::node()`
    * Use `\CuyZ\Valinor\Mapper\MappingError::messages()` instead
- Added `\CuyZ\Valinor\Mapper\MappingError::type()`
- Added `\CuyZ\Valinor\Mapper\MappingError::source()`
- Removed `\CuyZ\Valinor\Mapper\Tree\Node`
- Removed `\CuyZ\Valinor\Mapper\Tree\NodeTraverser`
- Removed `\CuyZ\Valinor\Mapper\Tree\Message\Messages::flattenFromNode()`
- Removed `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::node()`
    * Use the following methods instead:
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::name()`
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::path()`
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::type()`
        - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::sourceValue()`
- Changed `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::__construct()` signature
- Removed `\CuyZ\Valinor\Mapper\Source\IdentifiableSource`
- Removed `\CuyZ\Valinor\Utility\Priority\HasPriority`
- Removed `\CuyZ\Valinor\Utility\Priority\PrioritizedList`
- Mark `\CuyZ\Valinor\Mapper\Tree\Message\Messages::__construct` as `@internal`
- Mark `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::__construct` as `@internal`
- Mark `\CuyZ\Valinor\Normalizer\ArrayNormalizer::__construct` as `@internal`
- Mark `\CuyZ\Valinor\Normalizer\JsonNormalizer::__construct` as `@internal`
- Mark `\CuyZ\Valinor\Normalizer\StreamNormalizer::__construct` as `@internal`
