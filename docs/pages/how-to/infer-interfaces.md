# Inferring interfaces

When the mapper meets an interface, it needs to understand which implementation
will be used. This can be done by [registering a constructor for the interface],
but it can have limitations. A more powerful way to handle this is to infer the
implementation based on the data provided to the mapper. This can be done using
the `infer()` method.

The callback given to this method must return the name of a class that
implements the interface. Any arguments can be required by the callback; they
will be mapped properly using the given source.

If the callback can return several class names, it needs to provide a return
signature with the list of all class-strings that can be returned.

```php
$mapper = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(UuidInterface::class, fn () => MyUuid::class)
    ->infer(
        SomeInterface::class, 
        /** @return class-string<FirstImplementation|SecondImplementation> */
        fn (string $type) => match($type) {
            'first' => FirstImplementation::class,
            'second' => SecondImplementation::class,
            default => throw new DomainException("Unhandled type `$type`.")
        }
    )->mapper();

// Will return an instance of `FirstImplementation`
$mapper->map(SomeInterface::class, [
    'type' => 'first',
    'uuid' => 'a6868d61-acba-406d-bcff-30ecd8c0ceb6',
    'someString' => 'foo',
]);

// Will return an instance of `SecondImplementation`
$mapper->map(SomeInterface::class, [
    'type' => 'second',
    'uuid' => 'a6868d61-acba-406d-bcff-30ecd8c0ceb6',
    'someInt' => 42,
]);

interface SomeInterface {}

final class FirstImplementation implements SomeInterface
{
    public readonly UuidInterface $uuid;

    public readonly string $someString;
}

final class SecondImplementation implements SomeInterface
{
    public readonly UuidInterface $uuid;

    public readonly int $someInt;
}
```

## Inferring classes

The same mechanics can be applied to infer abstract or parent classes.

Example with an abstract class:

```php
abstract class SomeAbstractClass
{
    public string $foo;

    public string $bar;
}

final class SomeChildClass extends SomeAbstractClass
{
    public string $baz;
}

$result = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(
        SomeAbstractClass::class, 
        fn () => SomeChildClass::class
    )
    ->mapper()
    ->map(SomeAbstractClass::class, [
        'foo' => 'foo',
        'bar' => 'bar',
        'baz' => 'baz',
    ]);

assert($result instanceof SomeChildClass);
assert($result->foo === 'foo');
assert($result->bar === 'bar');
assert($result->baz === 'baz');
```

Example with inheritance:

```php
class SomeParentClass
{
    public string $foo;

    public string $bar;
}

final class SomeChildClass extends SomeParentClass
{
    public string $baz;
}

$result = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(
        SomeParentClass::class, 
        fn () => SomeChildClass::class
    )
    ->mapper()
    ->map(SomeParentClass::class, [
        'foo' => 'foo',
        'bar' => 'bar',
        'baz' => 'baz',
    ]);

assert($result instanceof SomeChildClass);
assert($result->foo === 'foo');
assert($result->bar === 'bar');
assert($result->baz === 'baz');
```

[registering a constructor for the interface]: use-custom-object-constructors.md#interface-implementation-constructor
