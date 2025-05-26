# Convert input during mapping

This library can automatically map most inputs to the expected types, but
sometimes it’s not enough, and custom logic must be applied to the data.

This is where mapper converters come in: they allow users to hook into the
mapping process and apply custom logic to the input, by defining a callable
signature that properly describes when it should be called:

- A first argument with a type matching the expected input being mapped
- A return type representing the targeted mapped type

These two types are enough for the library to know when to call the converters
and can contain advanced type annotations for more specific use cases.

Below is a basic example of a converter that converts string inputs to
uppercase:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        fn (string $value): string => strtoupper($value)
    )
    ->mapper()
    ->map('string', 'hello world'); // 'HELLO WORLD'
```

## Chaining converters

Converters can be chained, allowing multiple transformations to be applied to a
value. A second `callable` parameter can be declared, allowing the current
converter to call the next one in the chain.

A priority can be given to a converter to control the order in which converters
are applied. The higher the priority, the earlier the converter will be
executed. The default priority is 0.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        fn (string $value, callable $next): string => $next(strtoupper($value))
    )
    ->registerConverter(
        fn (string $value, callable $next): string => $next($value . '!'),
        priority: -10,
    )
    ->registerConverter(
        fn (string $value, callable $next): string => $next($value . '?'),
        priority: 10,
    )
    ->mapper()
    ->map('string', 'hello world'); // 'HELLO WORLD?!'
```

## Common converters examples

### Converting keys format from `snake_case` to `camelCase`

The following example is a classical use case where the input array keys are in
`snake_case` format, but the target object properties are in `camelCase` format.
A converter is used to convert the keys before mapping the values to the object.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        /**
         * Note that this converter will only be called when the input is an
         * array and the target type is an object.
         */
        function (array $values, callable $next): object {
            $camelCaseConverted = array_combine(
                array_map(
                    fn ($key) => lcfirst(str_replace('_', '', ucwords($key, '_'))),
                    array_keys($values),
                ),
                $values,
            );

            return $next($camelCaseConverted);
        }
    )
    ->mapper()
    ->map(User::class, [
        // Note that the input keys have the `snake_case` format, but the
        // properties of `User` have the `camelCase` format.
        'user_name' => 'John Doe',
        'birth_date' => '1971-11-08T00:00:00+00:00',
    ]);

final readonly class User
{
    public string $userName;
    public DateTimeInterface $birthDate;
}
```

### Renaming keys to match property names

The example below shows how to rename keys in the input array to match the
target object properties.

```php
function renameKeys(array $values, array $keyReplacements): array
{
    return array_combine(
        array_map(
            fn ($key) => $keyReplacements[$key] ?? $key,
            array_keys($values),
        ),
        $values,
    );
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        /**
         * Note that this converter will only be called when the input is an
         * array and the target type is an `Address`.
         */
        function (array $values, callable $next): Address {
            $convertedValues = renameKeys($values, [
                'avenue' => 'street',
                'town' => 'city',
            ]);

            return $next($convertedValues);
        }
    )
    ->mapper()
    ->map(Address::class, [
        // The key `avenue` does not match the property name `street`
        'avenue' => '221B Baker Street',
        'zipCode' => 'NW1 6XE',
        // The key `town` does not match the property name `city`
        'town' => 'London',
    ]);

final readonly class Address
{
    public string $street;
    public string $zipCode;
    public string $city;
}
```
