# Inferring interfaces

When the mapper meets an interface, it needs to understand which implementation
(a class that implements this interface) will be used — this information must be
provided in the mapper builder, using the method `infer()`.

The callback given to this method must return the name of a class that
implements the interface. Any arguments can be required by the callback; they
will be mapped properly using the given source.

```php
$mapper = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(UuidInterface::class, fn () => MyUuid::class)
    ->infer(SomeInterface::class, fn (string $type) => match($type) {
        'first' => FirstImplementation::class,
        'second' => SecondImplementation::class,
        default => throw new DomainException("Unhandled type `$type`.")
    })->mapper();

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
