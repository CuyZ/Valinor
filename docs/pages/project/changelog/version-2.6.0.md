# Changelog 2.6.0 — 11th of August 2026

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.6.0

## Notable changes

This release brings a set of new features to the library:

- [Provided mapper configurators](#provided-mapper-configurators)
- [Scalar value casting](#scalar-value-casting)
- [Mapping a property from a specific key](#mapping-a-property-from-a-specific-key)
- [New normalizer configurators](#new-normalizer-configurators)
- [Generics of PHP internal classes](#generics-of-php-internal-classes)
- [Default types for templates](#default-types-for-templates)
- [Overriding an unparseable type](#overriding-an-unparseable-type)

Enjoy! 🎉

---

### Provided mapper configurators

A set of configurators is now available out-of-the-box for the mapper, mirroring
the normalizer configurators introduced in the previous release. Each one can be
used either globally through the `configureWith()` method or locally as an
attribute targeting a specific property.

The `MapToDateTimeFromFormat` configurator parses the input string using the
given date format, which must follow the syntax supported by
`DateTimeImmutable::createFromFormat()`:

```php
use CuyZ\Valinor\Mapper\Configurator\MapToDateTimeFromFormat;
use CuyZ\Valinor\MapperBuilder;
use DateTimeInterface;

final readonly class Event
{
    public function __construct(
        public string $name,

        #[MapToDateTimeFromFormat('d/m/Y')]
        public DateTimeInterface $date,
    ) {}
}

$event = (new MapperBuilder())
    ->mapper()
    ->map(Event::class, [
        'name' => 'Release of legendary album',
        'date' => '08/11/1971', // mapped to a `DateTimeImmutable`
    ]);
```

The `MapExplodedStringToList` configurator explodes a string into a list using
the given separator, which is useful when the input carries a list as a single
delimited string, for instance a value coming from a CSV file or a query
parameter:

```php
use CuyZ\Valinor\Mapper\Configurator\MapExplodedStringToList;
use CuyZ\Valinor\MapperBuilder;

final readonly class Product
{
    public function __construct(
        public string $name,

        /** @var list<string> */
        #[MapExplodedStringToList(separator: ',')]
        public array $sizes,
    ) {}
}

$product = (new MapperBuilder())
    ->mapper()
    ->map(Product::class, [
        'name' => 'T-Shirt',
        'sizes' => 'XS,S,M,L,XL', // mapped to `['XS', 'S', 'M', 'L', 'XL']`
    ]);
```

The `MapArrayToList` configurator discards the keys of an array and maps its
values to a list, for cases where the input is an associative array, or a sparse
list with missing or out-of-order indices, that should be handled as a
sequential list:

```php
use CuyZ\Valinor\Mapper\Configurator\MapArrayToList;
use CuyZ\Valinor\MapperBuilder;

final readonly class Basket
{
    public function __construct(
        /** @var list<string> */
        #[MapArrayToList]
        public array $products,
    ) {}
}

$basket = (new MapperBuilder())
    ->mapper()
    ->map(Basket::class, [
        'a' => 'Coffee',
        'b' => 'Tea',
    ]); // mapped to `['Coffee', 'Tea']`
```

Finally, the `MapFromJson` configurator decodes a JSON string and hands the
result over to the mapper, so that the usual validation and error reporting
still apply to the decoded value:

```php
use CuyZ\Valinor\Mapper\Configurator\MapFromJson;
use CuyZ\Valinor\MapperBuilder;

final readonly class User
{
    public function __construct(
        public string $name,

        /** @var list<string> */
        #[MapFromJson]
        public array $roles,
    ) {}
}

$user = (new MapperBuilder())
    ->mapper()
    ->map(User::class, [
        'name' => 'John Doe',
        'roles' => '["admin", "editor"]', // mapped to `['admin', 'editor']`
    ]);
```

---

### Scalar value casting

Four configurators convert a scalar value to a specific type before mapping:
`MapAsBool`, `MapAsInt`, `MapAsFloat` and `MapAsString`. They are useful when
the input data carries values in a different representation than the targeted
type, for instance numbers or booleans encoded as strings in a form submission,
a CSV file or a JSON payload.

Used as an attribute, a single property is cast, leaving the strictness rules
untouched for every other value:

```php
use CuyZ\Valinor\Mapper\Configurator\MapAsBool;
use CuyZ\Valinor\Mapper\Configurator\MapAsInt;
use CuyZ\Valinor\MapperBuilder;

final readonly class User
{
    public function __construct(
        public string $name,

        #[MapAsInt]
        public int $age,

        #[MapAsBool(true: ['on', 'yes'], false: ['off', 'no'])]
        public bool $isActive,
    ) {}
}

$user = (new MapperBuilder())
    ->mapper()
    ->map(User::class, [
        'name' => 'John Doe',
        'age' => '42', // mapped to `42`
        'isActive' => 'on', // mapped to `true`
    ]);
```

Casting can also be enabled for every value of a given type with the new
`allowCastingToBoolean()`, `allowCastingToInteger()`, `allowCastingToFloat()`
and `allowCastingToString()` methods of the mapper builder. They offer a finer
control than `allowScalarValueCasting()`, which relaxes strictness for all
scalar types at once:

```php
use CuyZ\Valinor\MapperBuilder;

$age = (new MapperBuilder())
    ->allowCastingToInteger()
    ->mapper()
    ->map('int', '42'); // mapped to `42`
```

---

### Mapping a property from a specific key

The new `MapFromKey` attribute feeds a class property, or a constructor/method
argument, from a specific source key instead of matching it against the property
name:

```php
use CuyZ\Valinor\Mapper\Configurator\MapFromKey;
use CuyZ\Valinor\MapperBuilder;

final readonly class Person
{
    public function __construct(
        public string $name,

        #[MapFromKey('zipCode')]
        public string $postalCode,
    ) {}
}

$person = (new MapperBuilder())
    ->mapper()
    ->map(Person::class, [
        'name' => 'John Doe',
        'zipCode' => '75001', // mapped to `$postalCode`
    ]);
```

This attribute is built on a lightweight protocol that is open to userland: any
attribute class declaring a `mapKey(string $key): string` method and carrying
the `#[AsConverter]` attribute can remap the key of the element it is placed on.
This is handy to factor out a recurring transformation, such as a prefix shared
by several properties:

```php
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
#[\CuyZ\Valinor\Mapper\AsConverter]
final class MapWithPrefix
{
    public function __construct(private string $prefix) {}

    public function mapKey(string $key): string
    {
        return $this->prefix . $key;
    }
}

final readonly class Configuration
{
    public function __construct(
        #[MapWithPrefix('app_')] // reads from `app_host`
        public string $host,
        #[MapWithPrefix('app_')] // reads from `app_port`
        public int $port,
    ) {}
}
```

---

### New normalizer configurators

Three configurators join the ones introduced in the previous release.

The `NormalizeKeyTo` attribute renames the key of a property during
normalization, when the name used in the data format differs from the one used
in the PHP codebase:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeyTo;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

final readonly class Address
{
    public function __construct(
        public string $street,

        #[NormalizeKeyTo('town')]
        public string $city,
    ) {}
}

$addressAsArray = (new NormalizerBuilder())
    ->normalizer(Format::array())
    ->normalize(new Address('221B Baker Street', 'London'));

// [
//     'street' => '221B Baker Street',
//     'town' => 'London',
// ]
```

The `NormalizeToSingleValue` class flattens an object holding a single property,
so that instead of `['someProperty' => 'value']` the normalized result is simply
`'value'`. It can be used either as a configurator, applying to every object
with a single property, or as an attribute targeting a specific class or
property:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeToSingleValue;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

final readonly class Email
{
    public function __construct(
        public string $email,
    ) {}
}

final readonly class User
{
    public function __construct(
        public string $name,

        #[NormalizeToSingleValue]
        public Email $email,
    ) {}
}

$userAsArray = (new NormalizerBuilder())
    ->normalizer(Format::array())
    ->normalize(new User('John Doe', new Email('john.doe@example.com')));

// [
//     'name' => 'John Doe',
//     'email' => 'john.doe@example.com',
// ]
```

The `IgnoreOnNormalization` attribute excludes a property from the normalized
output, for instance to hide sensitive data such as a password. For the
attribute to take effect, an instance of this class must also be registered on
the builder via `configureWith()`:

```php
use CuyZ\Valinor\Normalizer\Configurator\IgnoreOnNormalization;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

final readonly class User
{
    public function __construct(
        public string $name,

        #[IgnoreOnNormalization]
        public string $password,
    ) {}
}

$userAsArray = (new NormalizerBuilder())
    ->configureWith(new IgnoreOnNormalization())
    ->normalizer(Format::array())
    ->normalize(new User('John Doe', 's3cr3t'));

// ['name' => 'John Doe']
```

---

### Generics of PHP internal classes

Generics used to be limited to userland classes, because classes internal to
PHP or provided by an extension cannot declare `@template` annotations in their
own source code. The library now ships generic signatures for a wide range of
them, including `ArrayObject`, `ArrayIterator`, the SPL data structures and the
`Ds` collection classes, so they can be parameterized like any other class:

```php
use CuyZ\Valinor\MapperBuilder;

$sizes = (new MapperBuilder())
    ->mapper()
    ->map('ArrayObject<string, int>', [
        'S' => 36,
        'M' => 38,
        'L' => 40,
    ]);
```

Every one of these templates declares a default type, so bare references like
`ArrayObject` keep resolving as before.

---

### Default types for templates

A `@template` annotation can now declare a default type with `=`. A template
that declares a default type may be omitted when the class is referenced, in
which case the default type is used:

```php
/**
 * @template TValue
 * @template TMeta of array<string, mixed> = array<string, string>
 */
final readonly class Page
{
    public function __construct(
        /** @var list<TValue> */
        public array $items,

        /** @var TMeta */
        public array $meta,
    ) {}
}

final readonly class SomeClass
{
    public function __construct(
        // `TMeta` is not filled in, its default type is used
        /** @var Page<string> */
        public Page $pageWithDefaultMeta,

        // `TMeta` is filled in, overriding its default type
        /** @var Page<string, array{cursor: int}> */
        public Page $pageWithCursorMeta,
    ) {}
}
```

A default type is what makes it possible to add a template to a class that is
already referenced elsewhere: the existing references, which do not fill the
new template in, keep resolving to its default type and can be made more
precise later on.

---

### Overriding an unparseable type

When a property, parameter or return type uses a PHPStan or Psalm syntax that
the library cannot parse yet, for instance a conditional type like
`($a is 1 ? int : null)`, the dedicated `@valinor-var`, `@valinor-param` and
`@valinor-return` annotations can be used to give the library a type it
understands. They take precedence over every other annotation, so the static
analysis tools keep using their own type while the library uses the override:

```php
final class SomeClass
{
    /**
     * @phpstan-param ($a is 1 ? int : null) $b
     * @valinor-param int|null $b
     */
    public function __construct(
        public readonly int $a,
        public readonly ?int $b,
    ) {}
}
```

---

### Features

* Add `@valinor-*` annotations to override an unparseable type ([11938c](https://github.com/CuyZ/Valinor/commit/11938c5aad733601b3fca956dac2818ee210ba11))
* Add default value support for `@template` annotations ([0d6efe](https://github.com/CuyZ/Valinor/commit/0d6efee4f9ef79859a4a7dca7bb1b8206aa13b3f))
* Add mapper builder methods to cast to scalar types ([cdca3f](https://github.com/CuyZ/Valinor/commit/cdca3f5f620544c7737e8f119f1ae7dc296214a4))
* Add mapper configurator `MapArrayToList` ([1f81fa](https://github.com/CuyZ/Valinor/commit/1f81fa39f8f52455203081d2fe281fb8144b075f))
* Add mapper configurator `MapAsBool` ([65dfed](https://github.com/CuyZ/Valinor/commit/65dfed04b7a539b49f63d0d99f906514e9f07320))
* Add mapper configurator `MapAsFloat` ([84eea9](https://github.com/CuyZ/Valinor/commit/84eea936cda08415cf907daa79c7f2af263cca89))
* Add mapper configurator `MapAsInt` ([ea28a7](https://github.com/CuyZ/Valinor/commit/ea28a7383eb2898264280c096e7fe9bf21dd36d5))
* Add mapper configurator `MapAsString` ([6b0528](https://github.com/CuyZ/Valinor/commit/6b05286da59af410e8ec703c138c4fa56c9daca7))
* Add mapper configurator `MapExplodedStringToList` ([beb4db](https://github.com/CuyZ/Valinor/commit/beb4dbb3d72db1846855711f87b8ce86e623f87c))
* Add mapper configurator `MapFromJson` ([469863](https://github.com/CuyZ/Valinor/commit/46986354b538b1640d6bf0c3e3273f9d8efb7213))
* Add mapper configurator `MapToDateTimeFromFormat` ([d6e53b](https://github.com/CuyZ/Valinor/commit/d6e53b7f6f521261c982627766149776c32efb5c))
* Add normalizer configurator `IgnoreOnNormalization` ([9769f2](https://github.com/CuyZ/Valinor/commit/9769f2c3a899cbd9451bc86057a3da1fcf8b0c92))
* Add normalizer configurator `NormalizeKeyTo` ([947127](https://github.com/CuyZ/Valinor/commit/947127a91fb9237143a6882c6b47340851cffd9f))
* Add normalizer configurator `NormalizeToSingleValue` ([7c5f13](https://github.com/CuyZ/Valinor/commit/7c5f13f7ce85d97871084644d1511af9f2929ab0))
* Allow mapping source keys with attributes ([631f66](https://github.com/CuyZ/Valinor/commit/631f66c29a9568413d65178faee65120aea45971))
* Support generics of PHP internal classes ([5419b4](https://github.com/CuyZ/Valinor/commit/5419b44534512b98a5459dbce77d876a0446c17a))

### Bug Fixes

* Bind the templates a constructor declares to the type being mapped ([0629d8](https://github.com/CuyZ/Valinor/commit/0629d862e32a0cc4037e2a82740e2e8a206b6e14))

### Internal

* Refactor HTTP request mapping ([578bd5](https://github.com/CuyZ/Valinor/commit/578bd58131db23c71f8aaccd75421988a2595c32))
* Remove `canCast()` and `cast()` from scalar types ([eae3f0](https://github.com/CuyZ/Valinor/commit/eae3f030ce1003209971bbc4cee3885344e8c15f))
* Unify shaped array and HTTP request node building ([3bb83b](https://github.com/CuyZ/Valinor/commit/3bb83b51ed2fbae664cb51563abd1cd227e1814b))
