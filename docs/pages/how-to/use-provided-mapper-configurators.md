# Using provided mapper configurators

This library provides a set of [mapper configurators] out-of-the-box that can be
used to apply common mapping behaviors:

[mapper configurators]: ./use-mapper-configurators.md

- [Restricting key case](#restricting-key-case)
- [Converting key case](#converting-key-case)
- [Mapping a property from a specific key](#mapping-a-property-from-a-specific-key)
- [Casting scalar values](#casting-scalar-values)

## Restricting key case

Four configurators restrict which key case is accepted when mapping input data
to objects or shaped arrays. If a key does not match the expected case, a
mapping error will be raised.

This is useful, for instance, to enforce a consistent naming convention across
an API's input to ensure that a JSON payload only contains `camelCase`,
`snake_case`, `PascalCase` or `kebab-case` keys.

Available configurators:

| Configurator                    | Example       |
|---------------------------------|---------------|
| `new RestrictKeysToCamelCase()` | `firstName`   |
| `new RestrictKeysToPascalCase()`| `FirstName`   |
| `new RestrictKeysToSnakeCase()` | `first_name`  |
| `new RestrictKeysToKebabCase()` | `first-name`  |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\RestrictKeysToCamelCase()
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'firstName' => 'John', // Ok
        'last_name' => 'Doe',  // Error
    ]);
```

## Converting key case

Two configurators are available to convert the keys of input data before mapping
them to object properties or shaped array keys. This allows accepting data with
a different naming convention than the one used in the PHP codebase.

### `MapKeysToCamelCase`

| Conversion                   |
|------------------------------|
| `first_name` → `firstName`   |
| `FirstName` → `firstName`    |
| `first-name` → `firstName`   |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\MapKeysToCamelCase()
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'first_name' => 'John', // mapped to `$firstName`
        'last_name' => 'Doe',   // mapped to `$lastName`
    ]);
```

### `MapKeysToSnakeCase`

| Conversion                    |
|-------------------------------|
| `firstName` → `first_name`   |
| `FirstName` → `first_name`   |
| `first-name` → `first_name`  |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\MapKeysToSnakeCase()
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'firstName' => 'John', // mapped to `$first_name`
        'lastName' => 'Doe',   // mapped to `$last_name`
    ]);
```

These configurators can be combined with a [restriction configurator] to both
validate and convert keys in a single step. The restriction configurator must be
registered *before* the conversion so that the validation runs on the original
input keys:

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\RestrictKeysToSnakeCase(),
        new \CuyZ\Valinor\Mapper\Configurator\MapKeysToCamelCase(),
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
```

[restriction configurator]: #restricting-key-case

## Mapping a property from a specific key

The `MapFromKey` attribute feeds a class property, or a constructor/method
argument, from a specific source key instead of matching it against the property
name. This is useful when the source data uses a key that differs from the name
of the property it should be mapped to.

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

The given key is used as-is: it is **not** affected by the key converters
registered with `registerKeyConverter()`, and the property name is no longer
accepted, the source is read only from the given key.

!!! note
    Two properties cannot be mapped from the same source key; doing so is a
    configuration error and throws an exception during mapping.

For a global renaming, or to declare a custom key mapping attribute, see the
[converting source keys chapter](convert-input.md#converting-source-keys).

## Casting scalar values

Several configurators convert a scalar value to a specific type before mapping.
This is useful when the input data carries values in a different representation
than the targeted type, for instance numbers or booleans encoded as strings in a
form submission, a CSV file or a JSON payload.

!!! note
    Scalar value casting can also be enabled globally, see [documentation about
    `MapperBuilder::allowScalarValueCasting()`](../usage/type-strictness-and-flexibility.md#allowing-scalar-value-casting).

### `MapAsBool`

Converts string and integer representations to a real `bool`. By default `1`,
`'1'` and `'true'` are converted to `true`, and `0`, `'0'` and `'false'` to
`false`. The accepted representations can be customized by giving the values that
should be converted to `true` and `false`.

Applied to a single property with the `#[MapAsBool]` attribute:

```php
use CuyZ\Valinor\Mapper\Configurator\MapAsBool;
use CuyZ\Valinor\MapperBuilder;

final readonly class User
{
    public function __construct(
        public string $name,

        #[MapAsBool(true: ['on', 'yes'], false: ['off', 'no'])]
        public bool $isActive,
    ) {}
}

$user = (new MapperBuilder())
    ->mapper()
    ->map(User::class, [
        'name' => 'John Doe',
        'isActive' => 'on', // mapped to `true`
    ]);
```

### `MapAsInt`

Converts a string representation of an integer to a real `int`. Any value that
is not a valid integer representation is left untouched and handed over to the
mapper.

Applied to a single property with the `#[MapAsInt]` attribute:

```php
use CuyZ\Valinor\Mapper\Configurator\MapAsInt;
use CuyZ\Valinor\MapperBuilder;

final readonly class User
{
    public function __construct(
        public string $name,

        #[MapAsInt]
        public int $age,
    ) {}
}

$user = (new MapperBuilder())
    ->mapper()
    ->map(User::class, [
        'name' => 'John Doe',
        'age' => '42', // mapped to `42`
    ]);
```

### `MapAsFloat`

Converts a string representation of a number to a real `float`. Any value that
is not a valid number representation is left untouched and handed over to the
mapper.

Applied to a single property with the `#[MapAsFloat]` attribute:

```php
use CuyZ\Valinor\Mapper\Configurator\MapAsFloat;
use CuyZ\Valinor\MapperBuilder;

final readonly class Product
{
    public function __construct(
        public string $name,

        #[MapAsFloat]
        public float $price,
    ) {}
}

$product = (new MapperBuilder())
    ->mapper()
    ->map(Product::class, [
        'name' => 'Coffee',
        'price' => '4.50', // mapped to `4.5`
    ]);
```
