# Changelog 2.5.0 — 28th of June 2026

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.5.0

## Notable changes

This release brings a set of new features to the library:

- [Normalizer configurators support](#normalizer-configurators-support)
- [Shaped list type support](#shaped-list-type-support)
- [`key-of` type support](#key-of-type-support)
- [and more!](#features)

Enjoy! 🎉

---

### Normalizer configurators support

A set of configurators is now available for the normalizer, mirroring the mapper
configurators introduced in the previous release. Each one can be used either
globally through the `configureWith()` method or locally as an attribute
targeting a specific class or property.

#### Keys case normalization

Four configurators normalize the keys of a normalized object to a given case.
This is useful to expose data following a naming convention that differs from
the one used in the PHP codebase.

| Configurator                      | Example                    |
|-----------------------------------|----------------------------|
| `new NormalizeKeysToCamelCase()`  | `first_name` → `firstName` |
| `new NormalizeKeysToPascalCase()` | `first_name` → `FirstName` |
| `new NormalizeKeysToSnakeCase()`  | `firstName` → `first_name` |
| `new NormalizeKeysToKebabCase()`  | `firstName` → `first-name` |

Used globally, the keys of every normalized object are converted:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToSnakeCase;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

$userAsArray = (new NormalizerBuilder())
    ->configureWith(new NormalizeKeysToSnakeCase())
    ->normalizer(Format::array())
    ->normalize($user);

// ['first_name' => 'John']
```

Used as an attribute, only the keys of the targeted class are converted:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToSnakeCase;

#[NormalizeKeysToSnakeCase]
final readonly class User
{
    public function __construct(
        public string $firstName,
    ) {}
}

// ['first_name' => 'John']
```

#### Date and time normalization

The `NormalizeDateTimeFormat` configurator normalizes any `DateTimeInterface`
instance to a string using the given format.

Used globally, every date and time encountered during normalization is formatted:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeDateTimeFormat;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

$userAsArray = (new NormalizerBuilder())
    ->configureWith(new NormalizeDateTimeFormat(DATE_ATOM))
    ->normalizer(Format::array())
    ->normalize($user);

// [
//     'name' => 'Jane Doe',
//     'createdAt' => '2000-01-01T00:00:00+00:00',
// ]
```

Used as an attribute, only the targeted property is formatted:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeDateTimeFormat;

final readonly class User
{
    public function __construct(
        public string $name,

        #[NormalizeDateTimeFormat(DATE_ATOM)]
        public DateTimeInterface $createdAt,
    ) {}
}
```

---

### Shaped list type support

The shaped list type `list{…}` is now supported. It works like a shaped array
but enforces sequential integer keys starting at `0`, making it the right type
to describe a tuple-like list of values.

```php
final readonly class SomeClass
{
    public function __construct(
        /** @var list{string, int, float} */
        public array $shapedList,

        /** @var list{0: string, 1: int} */
        public array $shapedListWithExplicitKeys,

        /** @var list{0: string, 1?: int} */
        public array $shapedListWithOptionalElement,

        /** @var list{string, int, ...} */
        public array $unsealedShapedList,

        /** @var list{string, int, ...list<float>} */
        public array $unsealedShapedListWithExplicitType,

        /** @var list{string, ...<float>} */
        public array $unsealedShapedListWithShorthandType,
    ) {}
}
```

---

### `key-of` type support

The `key-of<T>` type is now supported. It extracts the key types from enums,
arrays, lists, and shaped arrays, including array constants. It is compatible
with the same syntax as accepted by [PHPStan] and [Psalm].

```php
enum SomeBackedEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}

final readonly class SomeClassWithConstants
{
    public const SOME_ARRAY = ['foo' => 1, 'bar' => 2];
}

final readonly class SomeClass
{
    public function __construct(
        // Accepts 'FOO' or 'BAR' (the case names of the enum)
        /** @var key-of<SomeBackedEnum> */
        public string $enumKey,

        // Accepts 'foo' or 'bar' (the keys of the shaped array)
        /** @var key-of<array{foo: string, bar: int}> */
        public string $shapedArrayKey,

        // Accepts the key type of the array (string here)
        /** @var key-of<array<string, int>> */
        public string $arrayKey,

        // Accepts 'foo' or 'bar' (the keys of the class constant array)
        /** @var key-of<SomeClassWithConstants::SOME_ARRAY> */
        public string $constantArrayKey,
    ) {}
}
```

### Features

* Add normalizer configurator `ConvertDateTime` ([bf688b](https://github.com/CuyZ/Valinor/commit/bf688b28de0151cc32608d34b28c3c8a60191024))
* Add normalizer configurator `NormalizeKeysToCamelCase` ([b0d38f](https://github.com/CuyZ/Valinor/commit/b0d38f7a7f62ae6ab8446ae506bc28d842933089))
* Add normalizer configurator `NormalizeKeysToKebabCase` ([9826ca](https://github.com/CuyZ/Valinor/commit/9826ca22c8d6b259796bb96ee7d89ecd5ff72b07))
* Add normalizer configurator `NormalizeKeysToPascalCase` ([53bff2](https://github.com/CuyZ/Valinor/commit/53bff2be364b383ba4a995cac2df5d737b485868))
* Add normalizer configurator `NormalizeKeysToSnakeCase` ([c831e0](https://github.com/CuyZ/Valinor/commit/c831e05da41105e396107fb2dc7d59827c63764d))
* Add support for `key-of` type mapping ([ff16b2](https://github.com/CuyZ/Valinor/commit/ff16b2afb6fae951f6be354057515858bf0a4ccc))
* Add support for covariant templates ([c31f24](https://github.com/CuyZ/Valinor/commit/c31f247820658c0e0d8dd09597c9db4c5065bda6))
* Add support for shaped list type ([eeeb5c](https://github.com/CuyZ/Valinor/commit/eeeb5c6d70c809b7761a0f5d6b29667fd1141cdb))
* Support local alias types referencing other local aliases ([757256](https://github.com/CuyZ/Valinor/commit/757256e61f219f8662c5f076f36f0ac84eb53eb4))
* Support null values for class constants ([5cd356](https://github.com/CuyZ/Valinor/commit/5cd356b9442e400c4d4d9411731d9412a6e4cd4d))
* Support parenthesized union types ([21a04b](https://github.com/CuyZ/Valinor/commit/21a04b21317a3c2bb673a32c624030728ad205b1))

### Bug Fixes

* Prevent memory leak with functions' reflection ([6d36a0](https://github.com/CuyZ/Valinor/commit/6d36a03d72f0111a1b034f05165e01c8b3e24062))
* Rank union candidates by matching arguments ([126cf7](https://github.com/CuyZ/Valinor/commit/126cf77d2591c5f79ff24e7a9ac4b15803bccd11))

### Internal

* Add security vulnerability reporting guidelines ([b80e2a](https://github.com/CuyZ/Valinor/commit/b80e2a3b0b871e6af9afaa20a4db31f066bc6aa3))
* Memoize parent class definitions ([17d8cf](https://github.com/CuyZ/Valinor/commit/17d8cf665db41b84bdd24a6fb31b64d4f5f0f4db))
* Move int to float casting outside `Shell` ([f84e78](https://github.com/CuyZ/Valinor/commit/f84e786ecb206046093857c1bc5789875eb98876))

### Other

* Rename `ConvertDateTime` configurator to `NormalizeDateTimeFormat` ([b7683a](https://github.com/CuyZ/Valinor/commit/b7683ae973f2cbe8fcbdaa720032e90d8257dd7d))
* Rename `ConvertKeysTo*Case` configurators to `MapKeysTo*Case` ([e38e06](https://github.com/CuyZ/Valinor/commit/e38e06fede52960b5cc9f029310f5f34f3ee9f40))
