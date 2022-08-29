# Construction strategy

During the mapping, instances of objects are recursively created and hydrated
with transformed values. Construction strategies will determine what values are
needed and how an object is built.

## Native constructor

If a constructor exists and is public, its arguments will determine which values
are needed from the input.

```php
final class SomeClass
{
    public function __construct(
        public readonly string $foo,
        public readonly int $bar,
    ) {}
}
```

## Custom constructor

An object may have custom ways of being created, in such cases these
constructors need to be registered to the mapper to be used. A constructor is a
callable that can be either:

1. A named constructor, also known as a static factory method
2. The method of a service — for instance a repository
3. A "callable object" — a class that declares an `__invoke` method
4. Any other callable — including anonymous functions

In any case, the return type of the callable will be resolved by the mapper to
know when to use it. Any argument can be provided and will automatically be
mapped using the given source. These arguments can then be used to instantiate
the object in the desired way.

Registering any constructor will disable the native constructor — the
`__construct` method — of the targeted class. If for some reason it still needs
to be handled as well, the name of the class must be given to the
registration method.

If several constructors are registered, they must provide distinct signatures to
prevent collision during mapping — meaning that if two constructors require 
several arguments with the exact same names, the mapping will fail.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        // Allow the native constructor to be used
        Color::class,

        // Register a named constructor (1)
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

1.  …or for PHP < 8.1:
    
    ```php
    [Color::class, 'fromHex'],
    ```

### Dynamic constructors

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
        #[\CuyZ\Valinor\Attribute\DynamicConstructor]
        function (string $className, string $value): InterfaceWithStaticConstructor {
            return $className::from($value);
        }
    )
    ->mapper()
    ->map(ClassWithInheritedStaticConstructor::class, 'foo');
```

## Properties

If no constructor is registered, properties will determine which values are
needed from the input.

```php
final class SomeClass
{
    public readonly string $foo;

    public readonly int $bar;
}
```
