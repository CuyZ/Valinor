# Changelog 2.0.0 — 20th of June 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.0.0

First release of the v2 series! 🎉

This release introduces some new features but also backward compatibility breaks
that are detailed [in the upgrading chapter]: it is strongly recommended to read
it carefully before upgrading.

[in the upgrading chapter]: ../upgrading.md#upgrade-from-1x-to-2x

## Notable new features

**Mapper converters introduction**

A mapper converter allows users to hook into the mapping process and apply 
custom logic to the input, by defining a callable signature that properly
describes when it should be called:

- A first argument with a type matching the expected input being mapped
- A return type representing the targeted mapped type

These two types are enough for the library to know when to call the converter
and can contain advanced type annotations for more specific use cases.

Below is a basic example of a converter that converts string inputs to
uppercase:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        fn (string $value): string => strtoupper($value)
    )
    ->mapper()
    ->map('string', 'hello world'); // 'HELLO WORLD'
```

Converters can be chained, allowing multiple transformations to be applied to a
value. A second `callable` parameter can be declared, allowing the current
converter to call the next one in the chain.

A priority can be given to a converter to control the order in which converters
are applied. The higher the priority, the earlier the converter will be
executed. The default priority is 0.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        function(string $value, callable $next): string {
            return $next(strtoupper($value));
        }
    )
    ->registerConverter(
        function(string $value, callable $next): string {
            return $next($value . '!');
        },
        priority: -10,
    )
    ->registerConverter(
        function(string $value, callable $next): string {
            return $next($value . '?');
        },
        priority: 10,
    )
    ->mapper()
    ->map('string', 'hello world'); // 'HELLO WORLD?!'
```

More information can be found in the [mapper converter
chapter](../../how-to/convert-input.md).

**`NormalizerBuilder` introduction**

The `NormalizerBuilder` class has been introduced and will now be the main entry
to instantiate normalizers. Therefore, the methods in`MapperBuilder` that used
to configure and return normalizers have been removed.

This decision aims to make a clear distinction between the mapper and the 
normalizer configuration API, where confusion could arise when using both.

The `NormalizerBuilder` can be used like this:

```php
$normalizer = (new \CuyZ\Valinor\NormalizerBuilder())
    ->registerTransformer(
        fn (\DateTimeInterface $date) => $date->format('Y/m/d')
    )
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize($someData);
```

**Changes to messages/errors handling**

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

## Upgrading from 1.x to 2.x

[See the upgrading chapter](../upgrading.md#upgrade-from-1x-to-2x).

### ⚠ BREAKING CHANGES

* Add type and source accessors to `MappingError` ([783a24](https://github.com/CuyZ/Valinor/commit/783a2414a2e8c01a271c5301c04390ea01524ae0))
* Change exposed error messages codes ([a712d5](https://github.com/CuyZ/Valinor/commit/a712d5ba516b5bcdc962fb6c63470da66245a92d))
* Introduce `NormalizerBuilder` as the main entry for normalizers ([602a39](https://github.com/CuyZ/Valinor/commit/602a3998860cf88a600cea021777590b5bc9857f))
* Introduce internal cache interface and remove PSR-16 dependency ([10d9aa](https://github.com/CuyZ/Valinor/commit/10d9aa9a6d40af6b3edc1a215fca4d1be8e5b7de))
* Mark some class constructors as `@internal` ([3905ef](https://github.com/CuyZ/Valinor/commit/3905efac052c048f056714d1d1219c67b9abc392))
* Remove `MapperBuilder::alter()` in favor of mapper converters ([158cf7](https://github.com/CuyZ/Valinor/commit/158cf70145c3809a1ec7606fda45cce0320e3e72))
* Remove `MapperBuilder::enableFlexibleCasting()` ([1a805a](https://github.com/CuyZ/Valinor/commit/1a805a15d33a59f988f103e2920981f022f7a693))
* Remove unused class `PrioritizedList` ([074ca4](https://github.com/CuyZ/Valinor/commit/074ca405a5ac3e724d2526ed7a2f50f683eed21e))
* Remove unused interface `IdentifiableSource` ([74f63c](https://github.com/CuyZ/Valinor/commit/74f63cbfc140c737c2c58eb28be47b2dc6dcc10b))
* Rename `MapperBuilder::warmup()` method to `warmupCacheFor()` ([d9094b](https://github.com/CuyZ/Valinor/commit/d9094b55270cccf3ff232d2698904603e2af278e))
* Rework mapper node and messages handling ([07cdcf](https://github.com/CuyZ/Valinor/commit/07cdcf627b263177f1dba90fd5520c5aa5dbd48f))

### Features

* Allow `MapperBuilder` and `NormalizerBuilder` to clear cache ([1c2ac0](https://github.com/CuyZ/Valinor/commit/1c2ac0c22f4ce65ca13902e09a111faf4fc8c258))
* Introduce mapper converters to apply custom logic during mapping ([0ad0f9](https://github.com/CuyZ/Valinor/commit/0ad0f99b23c9f6888e3c955da5c451c58f42a455))

### Other

* Remove `Throwable` inheritance from `ErrorMessage` ([3e6024](https://github.com/CuyZ/Valinor/commit/3e6024a5652f95fd76a9993e273bed4d80d0fbd7))
* Remove old class doc block ([660356](https://github.com/CuyZ/Valinor/commit/660356273e5ccac11a17f6997f0e35e4168153ba))
* Remove unused property ([5ce829](https://github.com/CuyZ/Valinor/commit/5ce829864c63a92ce71f2974a26c572d61a95728))
