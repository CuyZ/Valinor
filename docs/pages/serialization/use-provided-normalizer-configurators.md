# Using provided normalizer configurators

This library provides a set of [normalizer configurators] out-of-the-box that
can be used to apply common normalization behaviors:

[normalizer configurators]: ./use-normalizer-configurators.md

- [Specifying date time normalization format](#specifying-date-time-normalization-format)
- [Converting key case](#converting-key-case)
- [Renaming property keys](#renaming-property-keys)
- [Flattening single property objects](#flattening-single-property-objects)
- [Ignoring properties](#ignoring-properties)

## Specifying date time normalization format

By default, dates will be formatted using the RFC 3339 format. The
`NormalizeDateTimeFormat` configurator can be used to specify which format to
use.

This class can be used either as a configurator for global usage or as an
attribute to target a specific property.

### Global usage as a configurator

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeDateTimeFormat;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

$userAsArray = (new NormalizerBuilder())
    ->configureWith(new NormalizeDateTimeFormat(\DateTimeInterface::ATOM))
    ->normalizer(Format::array())
    ->normalize($user);

// [
//     'name' => 'Jane Doe',
//     'createdAt' => '2000-01-01T00:00:00+00:00',
// ]
```

### Targeted usage as an attribute

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeDateTimeFormat;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

final readonly class User
{
    public function __construct(
        public string $name,

        #[NormalizeDateTimeFormat(\DateTimeInterface::ATOM)]
        public DateTimeInterface $createdAt,
    ) {}
}

$userAsArray = (new NormalizerBuilder())
    ->normalizer(Format::array())
    ->normalize($user);

// [
//     'name' => 'Jane Doe',
//     'createdAt' => '2000-01-01T00:00:00+00:00',
// ]
```

## Converting key case

Several configurators convert the keys of normalized objects to a different
naming convention than the one used in the PHP codebase.

| Configurator                | Result        |
|-----------------------------|---------------|
| `NormalizeKeysToSnakeCase`  | `first_name`  |
| `NormalizeKeysToCamelCase`  | `firstName`   |
| `NormalizeKeysToPascalCase` | `FirstName`   |
| `NormalizeKeysToKebabCase`  | `first-name`  |

Each of these classes can be used either as a configurator for global usage or
as an attribute to target a specific class.

### Global usage as a configurator

The keys of every normalized object are converted to the target case:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToSnakeCase;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

$userAsArray = (new NormalizerBuilder())
    ->configureWith(new NormalizeKeysToSnakeCase())
    ->normalizer(Format::array())
    ->normalize($user);

// [
//     'first_name' => 'John',
//     'last_name' => 'Doe',
// ]
```

### Targeted usage as an attribute

When used as an attribute, only the keys of the targeted class are converted,
leaving the rest of the output untouched:

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToSnakeCase;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

#[NormalizeKeysToSnakeCase]
final readonly class User
{
    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}
}

$userAsArray = (new NormalizerBuilder())
    ->normalizer(Format::array())
    ->normalize($user);

// [
//     'first_name' => 'John',
//     'last_name' => 'Doe',
// ]
```

## Renaming property keys

The name of a property in the data format may differ from the one used in the
PHP codebase. The `NormalizeKeyTo` attribute renames the key of a property
during normalization.

```php
use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeyTo;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

final readonly class Address
{
    public function __construct(
        public string $street,
        public string $zipCode,
        #[NormalizeKeyTo('town')]
        public string $city,
    ) {}
}

$addressAsArray = (new NormalizerBuilder())
    ->normalizer(Format::array())
    ->normalize(
        new Address(
            street: '221B Baker Street',
            zipCode: 'NW1 6XE',
            city: 'London', // Key will be renamed to 'town'
        )
    );

// [
//     'street' => '221B Baker Street',
//     'zipCode' => 'NW1 6XE',
//     'town' => 'London',
// ]
```

## Flattening single property objects

When an object holds a single property, it may be useful to flatten it so that
instead of `['someProperty' => 'value']` the normalized result is simply
`'value'`.

The `NormalizeToSingleValue` class can be used either as a configurator for
global usage or as an attribute to target a specific class or property.

### Global usage as a configurator

When used as a configurator, every object with a single property is flattened:

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

$value = (new NormalizerBuilder())
    ->configureWith(new NormalizeToSingleValue())
    ->normalizer(Format::array())
    ->normalize(new Email('john.doe@example.com'));

// 'john.doe@example.com'
```

### Targeted usage as an attribute

When used as an attribute, only the targeted class or property is flattened,
leaving the rest of the output untouched:

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

## Ignoring properties

A property can be excluded from the normalized output, for instance to hide
sensitive data such as a password, by marking it with the
`IgnoreOnNormalization` attribute.

!!! warning
    For the attribute to take effect, an `IgnoreOnNormalization` instance
    **must** also be registered on the builder via `configureWith()`. Without
    it, the property value is replaced by a placeholder object that raises an
    exception as soon as it is used (for instance when it is cast to a string
    or encoded to JSON), pointing to the missing registration.

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

// Registering the configurator is required for the attribute to take effect.
$userAsArray = (new NormalizerBuilder())
    ->configureWith(new IgnoreOnNormalization())
    ->normalizer(Format::array())
    ->normalize(new User('John Doe', 's3cr3t'));

// ['name' => 'John Doe']
```

## Custom transformer examples

Unlike the configurators above, the following behaviors are **not** provided
out-of-the-box: they illustrate real-life use-cases whose logic is specific to
each application. They are shown here as inspiration and must be implemented
with a custom transformer registered on the `NormalizerBuilder`, see the 
[extending the normalizer chapter](extending-normalizer.md).

### Transforming objects

Some objects can have custom behaviors during normalization, for instance
properties may need to be remapped. In the example below, a transformer will
check if an object defines a `normalize` method and use it if it exists.

<details>
<summary>Show code example — Custom object normalization</summary>

```php
namespace My\App;

final readonly class Address
{
    public function __construct(
        public string $road,
        public string $zipCode,
        public string $town,
    ) {}

    public function normalize(): array
    {
        return [
            'street' => $this->road,
            'postalCode' => $this->zipCode,
            'city' => $this->town,
        ];
    }
}

(new \CuyZ\Valinor\NormalizerBuilder())
    ->registerTransformer(function (object $object, callable $next) {
        return method_exists($object, 'normalize')
            ? $object->normalize()
            : $next();
    })
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\Address(
            road: '221B Baker Street',
            zipCode: 'NW1 6XE',
            town: 'London',
        ),
    );

// [
//     'street' => '221B Baker Street',
//     'postalCode' => 'NW1 6XE',
//     'city' => 'London',
// ]
```
</details>

### Versioning API

API versioning can be implemented with different strategies and algorithms. The
example below shows how objects can implement an interface to specify their own
specific versioning behavior.

<details>
<summary>Show code example — Versioning objects</summary>

```php
namespace My\App;

interface HasVersionedNormalization
{
    public function normalizeWithVersion(string $version): mixed;
}

final readonly class Address implements \My\App\HasVersionedNormalization
{
    public function __construct(
        public string $streetNumber,
        public string $streetName,
        public string $zipCode,
        public string $city,
    ) {}

    public function normalizeWithVersion(string $version): array
    {
        return match (true) {
            version_compare($version, '1.0.0', '<') => [
                // Street number and name are merged in a single property
                'street' => "$this->streetNumber, $this->streetName",
                'zipCode' => $this->zipCode,
                'city' => $this->city,
            ],
            default => get_object_vars($this),
        };
    }
}

function normalizeWithVersion(string $version): mixed
{
    return (new \CuyZ\Valinor\NormalizerBuilder())
        ->registerTransformer(
            fn (\My\App\HasVersionedNormalization $object) => $object->normalizeWithVersion($version)
        )
        ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
        ->normalize(
            new \My\App\Address(
                streetNumber: '221B',
                streetName: 'Baker Street',
                zipCode: 'NW1 6XE',
                city: 'London',
            )
        );
}

// Version can come for instance from HTTP request headers
$result_v0_4 = normalizeWithVersion('0.4');
$result_v1_8 = normalizeWithVersion('1.8');

// $result_v0_4 === [
//     'street' => '221B, Baker Street',
//     'zipCode' => 'NW1 6XE',
//     'city' => 'London',
// ]
// 
// $result_v1_8 === [
//     'streetNumber' => '221B',
//     'streetName' => 'Baker Street',
//     'zipCode' => 'NW1 6XE',
//     'city' => 'London',
// ]
```
</details>
