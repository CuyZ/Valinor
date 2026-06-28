# Using provided normalizer configurators

This library provides a set of [normalizer configurators] out-of-the-box that
can be used to apply common normalization behaviors:

[normalizer configurators]: ./use-normalizer-configurators.md

- [Specifying date time normalization format](#specifying-date-time-normalization-format)
- [Converting keys to “snake_case”](#converting-keys-to-snake_case)

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

## Converting keys to “snake_case”

The `NormalizeKeysToSnakeCase` configurator converts the keys of normalized
objects to `snake_case`. This allows producing output with a different naming
convention than the one used in the PHP codebase.

| Conversion                 |
|----------------------------|
| `firstName` → `first_name` |
| `FirstName` → `first_name` |

This class can be used either as a configurator for global usage or as an
attribute to target a specific class.

### Global usage as a configurator

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
