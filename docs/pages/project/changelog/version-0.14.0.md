# Changelog 0.14.0 — 1st of September 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.14.0

## Notable changes

Until this release, the behaviour of the date objects creation was very 
opinionated: a huge list of date formats were tested out, and if one was working
it was used to create the date.

This approach resulted in two problems. First, it led to (minor) performance 
issues, because a lot of date formats were potentially tested for nothing. More
importantly, it was not possible to define which format(s) were to be allowed
(and in result deny other formats).

A new method can now be used in the `MapperBuilder`:

```php
(new \CuyZ\Valinor\MapperBuilder())
    // Both `Cookie` and `ATOM` formats will be accepted
    ->supportDateFormats(DATE_COOKIE, DATE_ATOM)
    ->mapper()
    ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
```

Please note that the old behaviour has been removed. From now on, only valid 
timestamp or ATOM-formatted value will be accepted by default.

If needed and to help with the migration, the following **deprecated** 
constructor can be registered to reactivate the previous behaviour:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        new \CuyZ\Valinor\Mapper\Object\BackwardCompatibilityDateTimeConstructor()
    )
    ->mapper()
    ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
```

## ⚠ BREAKING CHANGES

* Introduce constructor for custom date formats ([f232cc](https://github.com/CuyZ/Valinor/commit/f232cc06363c447692e3df88129d0d31b506bccc))

## Features

* Handle abstract constructor registration ([c37ac1](https://github.com/CuyZ/Valinor/commit/c37ac1e25992d8b194b92d70e6a5727f71c665de))
* Introduce attribute `DynamicConstructor` ([e437d9](https://github.com/CuyZ/Valinor/commit/e437d9405cdff95b1cd9e644464fe38bf98ceb53))
* Introduce helper method to describe supported date formats ([11a7ea](https://github.com/CuyZ/Valinor/commit/11a7ea7252a788f8d9c075872dd82df78788aceb))

## Bug Fixes

* Allow trailing comma in shaped array ([bf445b](https://github.com/CuyZ/Valinor/commit/bf445b5364e11a91786b547ce14f3fe12043cf32))
* Correctly fetch file system cache entries ([48208c](https://github.com/CuyZ/Valinor/commit/48208c1ed1ffcc2ec93d9840b721ed6ce5a7670f))
* Detect invalid constructor handle type ([b3cb59](https://github.com/CuyZ/Valinor/commit/b3cb5927e9578b7519ef773abd0693d186ecaa68))
* Handle classes in a case-sensitive way in type parser ([254074](https://github.com/CuyZ/Valinor/commit/2540741171edce32ace1d59c9410a3bdd1e8e041))
* Handle concurrent cache file creation ([fd39ae](https://github.com/CuyZ/Valinor/commit/fd39aea2a79f216f4e39de52aced905ed75ead0d))
* Handle inherited private constructor in class definition ([73b622](https://github.com/CuyZ/Valinor/commit/73b62241b63ed365a90fb5e44876fafa63680177))
* Handle invalid nodes recursively ([a401c2](https://github.com/CuyZ/Valinor/commit/a401c2a2d696b62d73f0bcccc7fe5b28180ccf99))
* Prevent illegal characters in PSR-16 cache keys ([3c4d29](https://github.com/CuyZ/Valinor/commit/3c4d29901af1419ca39bf05e7d7008c8fe0b5ae6))
* Properly handle callable objects of the same class ([ae7ddc](https://github.com/CuyZ/Valinor/commit/ae7ddcf3caa77f2c4b4ad99dcdbb5f609de55335))

## Other

* Add singleton usage of `ClassStringType` ([4bc50e](https://github.com/CuyZ/Valinor/commit/4bc50e3e42d42798b266026827909b28b1ba8258))
* Change `ObjectBuilderFactory::for` return signature ([57849c](https://github.com/CuyZ/Valinor/commit/57849c92e736335ed2f6b9a50344a1a21dd3bc01))
* Extract native constructor object builder ([2b46a6](https://github.com/CuyZ/Valinor/commit/2b46a60f37966769e3694e743d92382c6e2f8842))
* Fetch attributes for function definition ([ec494c](https://github.com/CuyZ/Valinor/commit/ec494cec489b18a2344796c105af55a33dc39ba0))
* Refactor arguments instantiation ([6414e9](https://github.com/CuyZ/Valinor/commit/6414e9cf14f5c4bb4337dc2c8f2bfa09b7048fd0))
