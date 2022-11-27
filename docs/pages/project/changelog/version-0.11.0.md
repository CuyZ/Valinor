# Changelog 0.11.0 — 23rd of June 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.11.0

## Notable changes

**Strict mode**

The mapper is now more type-sensitive and will fail in the following situations:

- When a value does not match exactly the awaited scalar type, for instance a
  string `"42"` given to a node that awaits an integer.

- When unnecessary array keys are present, for instance mapping an array
  `['foo' => …, 'bar' => …, 'baz' => …]` to an object that needs only `foo` and
  `bar`.

- When permissive types like `mixed` or `object` are encountered.

These limitations can be bypassed by enabling the flexible mode:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->flexible()
    ->mapper();
    ->map('array{foo: int, bar: bool}', [
        'foo' => '42', // Will be cast from `string` to `int`
        'bar' => 'true', // Will be cast from `string` to `bool`
        'baz' => '…', // Will be ignored
    ]);
```

When using this library for a provider application — for instance an API 
endpoint that can be called with a JSON payload — it is recommended to use the
strict mode. This ensures that the consumers of the API provide the exact 
awaited data structure, and prevents unknown values to be passed.

When using this library as a consumer of an external source, it can make sense
to enable the flexible mode. This allows for instance to convert string numeric 
values to integers or to ignore data that is present in the source but not
needed in the application.

**Interface inferring**

It is now mandatory to list all possible class-types that can be inferred by the
mapper. This change is a step towards the library being able to deliver powerful 
new features such as compiling a mapper for better performance.

The existing calls to `MapperBuilder::infer` that could return several 
class-names must now add a signature to the callback. The callbacks that require
no parameter and always return the same class-name can remain unchanged.

For instance:

```php
$builder = (new \CuyZ\Valinor\MapperBuilder())
    // Can remain unchanged
    ->infer(SomeInterface::class, fn () => SomeImplementation::class);
```

```php
$builder = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(
        SomeInterface::class,
        fn (string $type) => match($type) {
            'first' => ImplementationA::class,
            'second' => ImplementationB::class,
            default => throw new DomainException("Unhandled `$type`.")
        }
    )
    // …should be modified with:
    ->infer(
        SomeInterface::class,
        /** @return class-string<ImplementationA|ImplementationB> */
        fn (string $type) => match($type) {
            'first' => ImplementationA::class,
            'second' => ImplementationB::class,
            default => throw new DomainException("Unhandled `$type`.")
        }
    );
```

**Object constructors collision**

All these changes led to a new check that runs on all registered object 
constructors. If a collision is found between several constructors that have the
same signature (the same parameter names), an exception will be thrown.

```php
final class SomeClass
{
    public static function constructorA(string $foo, string $bar): self
    {
        // …
    }

    public static function constructorB(string $foo, string $bar): self
    {
        // …
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        SomeClass::constructorA(...),
        SomeClass::constructorB(...),
    )
    ->mapper();
    ->map(SomeClass::class, [
        'foo' => 'foo',
        'bar' => 'bar',
    ]);

// Exception: A collision was detected […]
```

## ⚠ BREAKING CHANGES

* Handle exhaustive list of interface inferring ([1b0ff3](https://github.com/CuyZ/Valinor/commit/1b0ff39af650f1c5902ee930f49049042842ec08))
* Make mapper more strict and allow flexible mode ([90dc58](https://github.com/CuyZ/Valinor/commit/90dc586018449b15f3b0296241d8cb2d1320d940))

## Features

* Improve cache warmup ([44c5f1](https://github.com/CuyZ/Valinor/commit/44c5f13b70a14cbe1cb2b917acd127d14b8c7d14))
