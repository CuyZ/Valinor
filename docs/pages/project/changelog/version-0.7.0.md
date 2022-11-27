# Changelog 0.7.0 — 24th of March 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.7.0

## Notable changes

> **Warning** This release introduces a major breaking change that must be
considered before updating

**Constructor registration**

**The automatic named constructor discovery has been disabled**. It is now
mandatory to explicitly register custom constructors that can be used by the
mapper.

This decision was made because of a security issue reported by @Ocramius and
described in advisory [advisory GHSA-xhr8-mpwq-2rr2].

[advisory GHSA-xhr8-mpwq-2rr2]: https://github.com/CuyZ/Valinor/security/advisories/GHSA-5pgm-3j3g-2rc7

As a result, existing code must list all named constructors that were previously
automatically used by the mapper, and registerer them using the
method `MapperBuilder::registerConstructor()`.

The method `MapperBuilder::bind()` has been deprecated in favor of the method
above that should be used instead.

```php
final class SomeClass
{
    public static function namedConstructor(string $foo): self
    {
        // …
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        SomeClass::namedConstructor(...),
        // …or for PHP < 8.1:
        [SomeClass::class, 'namedConstructor'],
    )
    ->mapper()
    ->map(SomeClass::class, [
        // …
    ]);
```

See [documentation](https://github.com/CuyZ/Valinor#custom-constructor) for more
information.

---

**Source builder**

The `Source` class is a new entry point for sources that are not plain array or
iterable. It allows accessing other features like camel-case keys or custom
paths mapping in a convenient way.

It should be used as follows:

```php
$source = \CuyZ\Valinor\Mapper\Source\Source::json($jsonString)
    ->camelCaseKeys()
    ->map([
        'towns' => 'cities',
        'towns.*.label' => 'name',
    ]);

$result = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(SomeClass::class, $source);
```

See [documentation](https://github.com/CuyZ/Valinor#source) for more details
about its usage.

## ⚠ BREAKING CHANGES

* Change `Attributes::ofType` return type to `array` ([1a599b](https://github.com/CuyZ/Valinor/commit/1a599b0bdf5cf07385ed120817f1a4720064e4dc))
* Introduce method to register constructors used during mapping ([ecafba](https://github.com/CuyZ/Valinor/commit/ecafba3b21cd48f38a5e5d2b5b7f97012342536a))

## Features

* Introduce a path-mapping source modifier ([b7a7d2](https://github.com/CuyZ/Valinor/commit/b7a7d22993eb2758f5beb80e58e599df60258bf3))
* Introduce a source builder ([ad5103](https://github.com/CuyZ/Valinor/commit/ad51039cc3f76245eb96fab21a0f2709fcf13bdb))

## Bug Fixes

* Handle numeric key with camel case source key modifier ([b8a18f](https://github.com/CuyZ/Valinor/commit/b8a18feadcbb564d80a34ca004c584c56ac0de04))
* Handle parameter default object value compilation ([fdef93](https://github.com/CuyZ/Valinor/commit/fdef93074c32cf36072446da8a3466a1bf781dbb))
* Handle variadic arguments in callable constructors ([b646cc](https://github.com/CuyZ/Valinor/commit/b646ccecf25ca17ecf8fec4e03bb91748db9527d))
* Properly handle alias types for function reflection ([e5b515](https://github.com/CuyZ/Valinor/commit/e5b5157eaf0b60e10f1060bf22cc4e2bd7d828c7))

## Other

* Add Striker HTML report when running infection ([79c7a4](https://github.com/CuyZ/Valinor/commit/79c7a4563d545d5513f96ec1b5f00abbd50b5881))
* Handle class name in function definition ([e2451d](https://github.com/CuyZ/Valinor/commit/e2451df2c1857a935a1fc2e88c056d6b414ed924))
* Introduce functions container to wrap definition handling ([fd1117](https://github.com/CuyZ/Valinor/commit/fd11177b06c9740c621155c2f635e012c2f8651e))
