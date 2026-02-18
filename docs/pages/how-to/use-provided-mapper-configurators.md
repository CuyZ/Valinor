# Using provided mapper configurators

This library provides a set of [mapper configurators] out-of-the-box that can be
used to apply common mapping behaviors:

[mapper configurators]: ./use-mapper-configurators.md

- [Restricting key case](#restricting-key-case)
- [Converting key case](#converting-key-case)

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

### `ConvertKeysToCamelCase`

| Conversion                   |
|------------------------------|
| `first_name` → `firstName`   |
| `FirstName` → `firstName`    |
| `first-name` → `firstName`   |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase()
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'first_name' => 'John', // mapped to `$firstName`
        'last_name' => 'Doe',   // mapped to `$lastName`
    ]);
```

### `ConvertKeysToSnakeCase`

| Conversion                    |
|-------------------------------|
| `firstName` → `first_name`   |
| `FirstName` → `first_name`   |
| `first-name` → `first_name`  |

```php
$user = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \CuyZ\Valinor\Mapper\Configurator\ConvertKeysToSnakeCase()
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
        new \CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase(),
    )
    ->mapper()
    ->map(\My\App\User::class, [
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
```

[restriction configurator]: #restricting-key-case
