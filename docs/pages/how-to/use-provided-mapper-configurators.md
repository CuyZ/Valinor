# Using provided mapper configurators

This library provides a set of [mapper configurators] out-of-the-box that can be
used to apply common mapping behaviors:

[mapper configurators]: ./use-mapper-configurators.md

- [Restricting key case](#restricting-key-case)

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
