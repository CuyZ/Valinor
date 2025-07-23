# Using custom object constructors

An object may have custom ways of being created, in such cases these
constructors need to be registered to the mapper to be used. A constructor can
be either:

1. A named constructor, also known as a static factory method
2. The method of a service — for instance a repository
3. A "callable object" — a class that declares an `__invoke` method
4. Any other callable — including anonymous functions

In any case, the return type of the callable will be resolved by the mapper to
know when to use it. Any argument can be provided and will automatically be
mapped using the given source. These arguments can then be used to instantiate
the object in the desired way.

If several constructors are registered, they must provide distinct signatures to
prevent collision during mapping — meaning that if two constructors require
several arguments with the exact same names, the mapping will fail.

!!! note

    Registering a constructor for a class will prevent its native constructor 
    (the `__construct` method) to be handled by the mapper. If it needs to be
    be enabled again, it has to be explicitly registered.

## The `Constructor` attribute

The quickest and easiest way to register a constructor is to use the
`Constructor` attribute, which will mark a method as a constructor that can be
used by the mapper.

The targeted method must be public, static and return an instance of the class
it is part of.

```php
final readonly class Email
{
    // When another constructor is registered for the class, the native
    // constructor is disabled. To enable it again, it is mandatory to
    // explicitly register it again.
    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public function __construct(public string $value) {}

    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public static function createFrom(string $userName, string $domainName): self
    {
        return new self($userName . '@' . $domainName);
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(Email::class, [
        'userName' => 'john.doe',
        'domainName' => 'example.com',
    ]); // john.doe@example.com
```

## Manually registering a constructor

There are cases where the `Constructor` attribute cannot be used, for instance
when the class is an external dependency that cannot be modified. In such cases,
the `registerConstructor` method can be used to register any callable as a
constructor.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        // When another constructor is registered for the class, the native
        // constructor is disabled. To enable it again, it is mandatory to
        // explicitly register it again by giving the class name to this method.
        Color::class,

        // Register a named constructor
        Color::fromHex(...),

        /**
         * An anonymous function can also be used, for instance when the desired
         * object is an external dependency that cannot be modified.
         * 
         * @param 'red'|'green'|'blue' $color
         * @param 'dark'|'light' $darkness
         */
        function (string $color, string $darkness): Color {
            $main = $darkness === 'dark' ? 128 : 255;
            $other = $darkness === 'dark' ? 0 : 128;
 
            return new Color(
                $color === 'red' ? $main : $other,
                $color === 'green' ? $main : $other,
                $color === 'blue' ? $main : $other,
            );
        }
    )
    ->mapper()
    ->map(Color::class, [/* … */]);

final class Color
{
    /**
     * @param int<0, 255> $red
     * @param int<0, 255> $green
     * @param int<0, 255> $blue
     */
    public function __construct(
        public readonly int $red,
        public readonly int $green,
        public readonly int $blue
    ) {}

    /**
     * @param non-empty-string $hex
     */
    public static function fromHex(string $hex): self
    {
        if (strlen($hex) !== 6) {
            throw new DomainException('Must be 6 characters long');
        }

        /** @var int<0, 255> $red */
        $red = hexdec(substr($hex, 0, 2));
        /** @var int<0, 255> $green */
        $green = hexdec(substr($hex, 2, 2));
        /** @var int<0, 255> $blue */
        $blue = hexdec(substr($hex, 4, 2));

        return new self($red, $green, $blue);
    }
}
```

## Interface implementation constructor

By default, the mapper cannot instantiate an interface, as it does not know
which implementation to use. To do so, it is possible to register a constructor
for an interface, in the same way as for a class.

!!! note

    Because the mapper cannot automatically guess which implementation can be
    used for an interface, it is not possible to use the `Constructor`
    attribute, the `MapperBuilder::registerConstructor()` method must be used
    instead.

In the example below, the mapper is taught how to instantiate an implementation
of `UuidInterface` from package [`ramsey/uuid`](https://github.com/ramsey/uuid):

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        // The static method below has return type `UuidInterface`; therefore,
        // the mapper will build an instance of `Uuid` when it needs to 
        // instantiate an implementation of `UuidInterface`.
        Ramsey\Uuid\Uuid::fromString(...)
    )
    ->mapper()
    ->map(
        Ramsey\Uuid\UuidInterface::class,
        '663bafbf-c3b5-4336-b27f-1796be8554e0'
    );
```

## Custom enum constructor

Registering a constructor for an enum works the same way as for a class, as
described above.

```php
enum Color: string
{
    case LIGHT_RED = 'LIGHT_RED';
    case LIGHT_GREEN = 'LIGHT_GREEN';
    case LIGHT_BLUE = 'LIGHT_BLUE';
    case DARK_RED = 'DARK_RED';
    case DARK_GREEN = 'DARK_GREEN';
    case DARK_BLUE = 'DARK_BLUE';

    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public static function fromMatrix(string $type, string $color): Color
    {
        return self::from($type . '_' . $color);
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(Color::class , [
        'type' => 'DARK',
        'color' => 'RED'
    ]); // Color::DARK_RED
```

!!! note

    An enum constructor can target a specific pattern:

    ```php
    enum Color: string
    {
        case LIGHT_RED = 'LIGHT_RED';
        case LIGHT_GREEN = 'LIGHT_GREEN';
        case LIGHT_BLUE = 'LIGHT_BLUE';
        case DARK_RED = 'DARK_RED';
        case DARK_GREEN = 'DARK_GREEN';
        case DARK_BLUE = 'DARK_BLUE';
    
        /**
         * This constructor will be called only when pattern `SomeEnum::DARK_*`
         * is requested during mapping.
         *
         * @return Color::DARK_*
         */
        #[\CuyZ\Valinor\Mapper\Object\Constructor]
        public static function darkFrom(string $value): Color
        {
            return self::from('DARK_' . $value);
        }
    }
    
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(Color::class . '::DARK_*', 'RED'); // Color::DARK_RED
    ```

## Dynamic constructors

In some situations the type handled by a constructor is only known at runtime,
in which case the constructor needs to know what class must be used to
instantiate the object.

For instance, an interface may declare a static constructor that is then
implemented by several child classes. One solution would be to register the
constructor for each child class, which leads to a lot of boilerplate code and
would require a new registration each time a new child is created. Another way
is to use the attribute `\CuyZ\Valinor\Mapper\Object\DynamicConstructor`.

When a constructor uses this attribute, its first parameter must be a string and
will be filled with the name of the actual class that the mapper needs to build
when the constructor is called. Other arguments may be added and will be mapped
normally, depending on the source given to the mapper.

```php
interface InterfaceWithStaticConstructor
{
    public static function from(string $value): self;
}

final class ClassWithInheritedStaticConstructor implements InterfaceWithStaticConstructor
{
    private function __construct(private SomeValueObject $value) {}

    public static function from(string $value): self
    {
        return new self(new SomeValueObject($value));
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        #[\CuyZ\Valinor\Mapper\Object\DynamicConstructor]
        function (string $className, string $value): InterfaceWithStaticConstructor {
            return $className::from($value);
        }
    )
    ->mapper()
    ->map(ClassWithInheritedStaticConstructor::class, 'foo');
```
