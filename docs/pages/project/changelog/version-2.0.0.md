# Changelog 2.0.0 — 27th of June 2025

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

* Add purity markers in `MapperBuilder` and `NormalizerBuilder` ([123058](https://github.com/CuyZ/Valinor/commit/12305846c2444533abff2bb343dc046d249139a9))
* Add type and source accessors to `MappingError` ([378141](https://github.com/CuyZ/Valinor/commit/37814108beeda6d0200dab780fd787cb1b947899))
* Change exposed error messages codes ([15bb11](https://github.com/CuyZ/Valinor/commit/15bb115d6f96ccbfc0bcc2a05227a4535abf9b11))
* Introduce `NormalizerBuilder` as the main entry for normalizers ([f79ce2](https://github.com/CuyZ/Valinor/commit/f79ce2b8cfd78120fcd5adef7c7aee3a049f5d3f))
* Introduce internal cache interface and remove PSR-16 dependency ([dfdf40](https://github.com/CuyZ/Valinor/commit/dfdf403c0f12326558c7675b1464b77197c1869f))
* Mark some class constructors as `@internal` ([7fe5fe](https://github.com/CuyZ/Valinor/commit/7fe5fe5a6d86ec7b9280a11a4b77173b5f0af6ae))
* Remove `MapperBuilder::alter()` in favor of mapper converters ([bee098](https://github.com/CuyZ/Valinor/commit/bee098b476639ab200c8320b8690f754876c40f4))
* Remove `MapperBuilder::enableFlexibleCasting()` ([f8f16d](https://github.com/CuyZ/Valinor/commit/f8f16df28288459cd3f3e2bc8bb1e0fa2f5e882c))
* Remove unused class `PrioritizedList` ([0b8c89](https://github.com/CuyZ/Valinor/commit/0b8c89a3fe4f7f0eccfc8cd4054f6e9f0fc3d3c1))
* Remove unused interface `IdentifiableSource` ([aefb20](https://github.com/CuyZ/Valinor/commit/aefb203ddf14fa656e6f72da4daea6366898249c))
* Rename `MapperBuilder::warmup()` method to `warmupCacheFor()` ([963156](https://github.com/CuyZ/Valinor/commit/963156a2f23d15388b24316e841299fe10dd296f))
* Rework mapper node and messages handling ([14d5ca](https://github.com/CuyZ/Valinor/commit/14d5ca3f393cff26eea93559b0bcb8916e16f8d7))

### Features

* Allow `MapperBuilder` and `NormalizerBuilder` to clear cache ([fe318c](https://github.com/CuyZ/Valinor/commit/fe318c6c30bab11d9dcdf0138ae6fc74d6cc8d3d))
* Introduce mapper converters to apply custom logic during mapping ([46c823](https://github.com/CuyZ/Valinor/commit/46c823e9fe85bc2771b587eaeecbab11c5994f5b))

### Bug Fixes

* Update file system cache entries permissions ([6ffb0f](https://github.com/CuyZ/Valinor/commit/6ffb0f3266d09bebdaa40558210906de6d269d7a))

### Other

* Remove `Throwable` inheritance from `ErrorMessage` ([dbd731](https://github.com/CuyZ/Valinor/commit/dbd7312050d29b30574acc3d4c132d48e378c219))
* Remove old class doc block ([4c2194](https://github.com/CuyZ/Valinor/commit/4c2194ebbbebf0b0e1f729a11a39828f263d7cb2))
* Remove unused property ([53841a](https://github.com/CuyZ/Valinor/commit/53841ac79f6ce0314ded278013f9a5d66defd91f))
