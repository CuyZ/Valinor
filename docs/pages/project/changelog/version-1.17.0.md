# Changelog 1.17.0 — 20th of June 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.17.0

## Notable changes

**Flexible casting setting split**

The mapper setting `enableFlexibleCasting` is (softly) deprecated in favor of
three distinct modes, which guarantee the same functionalities as before.

*Allowing scalar value casting:*

With this setting enabled, scalar types will accept castable values:

- Integer types will accept any valid numeric value, for instance the
  string value `"42"`.

- Float types will accept any valid numeric value, for instance the
  string value `"1337.42"`.

- String types will accept any integer, float or object implementing the
  `Stringable` interface.

- Boolean types will accept any truthy or falsy value:
    * `(string) "true"`, `(string) "1"` and `(int) 1` will be cast to `true`
    * `(string) "false"`, `(string) "0"` and `(int) 0` will be cast to `false`

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowScalarValueCasting()
    ->mapper()
    ->map('array{id: string, price: float, active: bool}', [
        'id' => 549465210, // Will be cast to string
        'price' => '42.39', // Will be cast to float
        'active' => 1, // Will be cast to bool
    ]);
```

*Allowing non-sequential lists:*

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

*Allowing undefined values:*

Allows the mapper to accept undefined values (missing from the input), by
converting them to `null` (if the current type is nullable) or an empty array (if the current type is an object or an iterable).

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

### Features

* Split flexible casting setting in three distinct modes ([02ef8e](https://github.com/CuyZ/Valinor/commit/02ef8e79ca106db77209a6b97ffacd2a5d8c44b6))

### Other

* Simplify `ValueNode` implementation ([7e6ccf](https://github.com/CuyZ/Valinor/commit/7e6ccf8422b784e41418209691f100cede11c39d))
