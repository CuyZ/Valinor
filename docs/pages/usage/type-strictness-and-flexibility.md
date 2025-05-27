# Type strictness & flexibility

The mapper provided by this library is designed to be as strict as possible.
Any deviation from the expected types or structures will result in a mapping
error.

1. Scalar types will only accept values that strictly match their type.
2. Objects will only accept values that can be mapped to their constructor
   parameters, unneeded array entries will cause a mapping error.
3. Arrays and lists will only accept values that strictly match their subtypes
   and structure.

Types like `mixed` or `object` are too permissive and not permitted, because
they are not precise enough.

If these limitations are too restrictive, the mapper can be made more flexible
to disable one or several rule(s) declared above.

## Allowing scalar value casting

With this setting enabled, scalar types will accept castable values:

- Integer types will accept any valid numeric value, for instance the string
  value `"42"`.

- Float types will accept any valid numeric value, for instance the string value
  `"1337.42"`.

- String types will accept any integer, float or object implementing the
  `Stringable` interface.

- Boolean types will accept any truthy or falsy value:
    * `(string) "true"`, `(string) "1"` and `(int) 1` will be cast to `true`
    * `(string) "false"`, `(string) "0"` and `(int) 0` will be cast to `false`

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowNonSequentialList()
    ->mapper()
    ->map('array{id: string, price: float, active: bool}', [
        'id' => 549465210, // Will be cast to string
        'price' => '42.39', // Will be cast to float
        'active' => 1, // Will be cast to bool
    ]);
```

## Allowing non-sequential lists

By default, list types will only accept sequential keys starting from 0.

This setting allows the mapper to convert associative arrays to a list with
sequential keys.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowNonSequentialList()
    ->mapper()
    ->map('list<int>', [
        'foo' => 42,
        'bar' => 1337,
    ]);

// => [0 => 42, 1 => 1337]
```

## Allowing undefined values

Allows the mapper to accept undefined values (missing from the input), by 
converting them to `null` (if the current type is nullable) or an empty array
(if the current type is an object or an iterable).

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowUndefinedValues()
    ->mapper()
    ->map('array{name: string, age: int|null}', [
        'name' => 'John Doe',
        // 'age' is not defined
    ]);

// => ['name' => 'John Doe', 'age' => null]
```

## Allowing superfluous keys

By default, an error is raised when a source array contains keys that do not
match a class property/parameter or a shaped array element.

This setting allows the mapper to ignore these superfluous keys.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowSuperfluousKeys()
    ->mapper()
    ->map('array{name: string, age: int}', [
        'name' => 'John Doe',
        'age' => 42,
        'city' => 'Paris', // Will be ignored
    ]);
```

## Allowing permissive types

Allows permissive types `mixed` and `object` to be used during mapping.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowPermissiveTypes()
    ->mapper()
    ->map('array{name: string, data: mixed}', [
        'name' => 'some_product',
        'data' => 42, // Could be any value
    ]);
```
