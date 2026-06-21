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

final readonly class FirstImplementation implements SomeInterface
{
    public UuidInterface $uuid;

    public string $someString;
}

final readonly class SecondImplementation implements SomeInterface
{
    public UuidInterface $uuid;

    public int $someInt;
}
```

## Inferring classes

The same mechanics can be applied to infer abstract or parent classes.

Example with an abstract class:

```php
abstract readonly class SomeAbstractClass
{
    public string $foo;

    public string $bar;
}

final readonly class SomeChildClass extends SomeAbstractClass
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
readonly class SomeParentClass
{
    public string $foo;

    public string $bar;
}

final readonly class SomeChildClass extends SomeParentClass
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

## Inferring generic classes

It can sometimes be useful for an inferred interface implementation to be a
generic class. To do so, it is not possible to use the `class-string` return
type, because by nature a class-string cannot be generic.

To do so, a workaround is implemented: instead of returning a `class-string`,
the callback can return a string value containing the signature of the generic
class.

```php
namespace My\App;

interface ApiResponse {}

/**
 * @template T
 */
final readonly class SuccessResponse implements ApiResponse
{
    /** @var T */
    public mixed $data;
}

final readonly class User
{
    public string $name;
    public string $email;
}

final readonly class Product
{
    public string $name;
    public float $price;
}

$mapper = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(
        ApiResponse::class,
        /** @return '\My\App\SuccessResponse<\My\App\User>'|'\My\App\SuccessResponse<\My\App\Product>' */
        static fn (string $type): string => match($type) {
            'user' => SuccessResponse::class . '<' . User::class . '>',
            'product' => SuccessResponse::class . '<' . Product::class . '>',
            default => throw new \DomainException("Unhandled type `$type`."),
        }
    )
    ->mapper();

$userResponse = $mapper->map(ApiResponse::class, [
    'type' => 'user', // Will return a `SuccessResponse<User>`
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

assert($userResponse instanceof SuccessResponse);
assert($userResponse->data instanceof User);

$productResponse = $mapper->map(ApiResponse::class, [
    'type' => 'product', // Will return a `SuccessResponse<Product>`
    'name' => 'Laptop',
    'price' => 1337.42,
]);

assert($productResponse instanceof SuccessResponse);
assert($productResponse->data instanceof Product);
```
