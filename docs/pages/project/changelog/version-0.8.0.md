# Changelog 0.8.0 — 9th of May 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.8.0

## Notable changes

**Float values handling**

Allows the usage of float values, as follows:

```php
class Foo
{
    /** @var 404.42|1337.42 */
    public readonly float $value;
}
```

---

**Literal boolean `true` / `false` values handling**

Thanks @danog for this feature!

Allows the usage of boolean values, as follows:

```php
class Foo
{
    /** @var int|false */
    public readonly int|bool $value;
}
```

---

**Class string of union of object handling**

Allows to declare several class names in a `class-string`:

```php
class Foo
{
    /** @var class-string<SomeClass|SomeOtherClass> */
    public readonly string $className;
}
```

---

**Allow `psalm` and `phpstan` prefix in docblocks**

Thanks @boesing for this feature!

The following annotations are now properly handled: `@psalm-param`,
`@phpstan-param`, `@psalm-return` and `@phpstan-return`.

If one of those is found along with a basic `@param` or `@return` annotation, it
will take precedence over the basic value.

## Features

* Allow `psalm` and `phpstan` prefix in docblocks ([64e0a2](https://github.com/CuyZ/Valinor/commit/64e0a2d5ac727062c7c9c45f636081d1065f2bb9))
* Handle class string of union of object ([b7923b](https://github.com/CuyZ/Valinor/commit/b7923bc383f55095683e38a6da760a90df156edd))
* Handle filename in function definition ([0b042b](https://github.com/CuyZ/Valinor/commit/0b042bc495caf30f3a4d6e72547f65dae69282ec))
* Handle float value type ([790df8](https://github.com/CuyZ/Valinor/commit/790df8a3b8e9b608e33086f673372b8dff7775c7))
* Handle literal boolean `true` / `false` types ([afcedf](https://github.com/CuyZ/Valinor/commit/afcedf9e56100e3e69c340172b40cd6deb471f64))
* Introduce composite types ([892f38](https://github.com/CuyZ/Valinor/commit/892f3831c221c93f74edc4ec14b56c281cf2438e))

## Bug Fixes

* Call value altering function only if value is accepted ([2f08e1](https://github.com/CuyZ/Valinor/commit/2f08e1a9b306a1d1ae0e9636049816a9c8bd0b92))
* Handle function definition cache invalidation when file is modified ([511a0d](https://github.com/CuyZ/Valinor/commit/511a0dfee8ab51f9d220df03f063aead603298ba))

## Other

* Add configuration for Composer allowed plugins ([2f310c](https://github.com/CuyZ/Valinor/commit/2f310cf5ab9f36bf98d338a99a536279049d1cef))
* Add Psalm configuration file to `.gitattributes` ([979272](https://github.com/CuyZ/Valinor/commit/9792722e4f17507c73cd243ad3f23053e24278aa))
* Bump dev-dependencies ([844384](https://github.com/CuyZ/Valinor/commit/8443847cb8a7c4810ff8f71cb88487762f1532c1))
* Declare code type in docblocks ([03c84a](https://github.com/CuyZ/Valinor/commit/03c84a1f09b5e4cc567a5640f660a60db860c23c))
* Ignore `Polyfill` coverage ([c08fe5](https://github.com/CuyZ/Valinor/commit/c08fe5a3c53618a9e67d2aaa5f6c9d51f7d482c9))
* Remove `symfony/polyfill-php80` dependency ([368737](https://github.com/CuyZ/Valinor/commit/368737921787a12cd0bd04b5161f36c487e62b64))
