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

Converters can be chained, allowing multiple conversions to be applied to a
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

## Attribute converters

Callable converters allow targeting any value during mapping, whereas attribute
converters allow targeting a specific class or property for a more granular
control.

To be detected by the mapper, an attribute class must be registered first by
adding the `AsConverter` attribute to it.

Attributes must declare a method named `map` that follows the same rules as
callable converters: a mandatory first parameter and an optional second
`callable` parameter.

Below is an example of an attribute converter that converts string inputs to
boolean values based on specific string inputs:

```php
namespace My\App;

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class CastToBool
{
    /**
     * @param callable(mixed): bool $next
     */
    public function map(string $value, callable $next): bool
    {
        $value = match ($value) {
            'yes', 'on' => true,
            'no', 'off' => false,
            default => $value,
        };
        
        return $next($value);
    }
}

final readonly class User
{
    public string $name;
    
    #[\My\App\CastToBool]
    public bool $isActive;
}

$user = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(User::class, [
        'name' => 'John Doe',
        'isActive' => 'yes',
    ]);

$user->name === 'John Doe';
$user->isActive === true;
```

Attribute converters can also be used on function parameters when mapping
arguments:

```php
function someFunction(string $name, #[\My\App\CastToBool] bool $isActive) {
    // …
};

$arguments = (new \CuyZ\Valinor\MapperBuilder())
    ->argumentsMapper()
    ->mapArguments(someFunction(...), [
        'name' => 'John Doe',
        'isActive' => 'yes',
    ]);

$arguments['name'] === 'John Doe';
$arguments['isActive'] === true;
```

---

When there is no control over the converter attribute class, it is possible to
register it using the `registerConverter` method.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(\Some\External\ConverterAttribute::class)
    ->mapper()
    ->map(…);
```

It is also possible to register attributes that share a common interface by
giving the interface name to the registration method.

```php
namespace My\App;

interface SomeAttributeInterface {}

#[\Attribute]
final class SomeAttribute implements \My\App\SomeAttributeInterface {}

#[\Attribute]
final class SomeOtherAttribute implements \My\App\SomeAttributeInterface {}

(new \CuyZ\Valinor\MapperBuilder())
    // Registers both `SomeAttribute` and `SomeOtherAttribute` attributes
    ->registerConverter(\My\App\SomeAttributeInterface::class)
    ->mapper()
    ->map(…);
```

## Converters error handling

When a converter fails to convert the input, it may throw an exception. When
this happens, the mapper will properly handle the exception only if it follows
the rules defined in the [validation and error handling section].

Example:

```php
namespace My\App;

final class CustomDateException extends \RuntimeException { }

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class DateTimeFormat
{
    public function __construct(
        /** @var non-empty-string */
        private string $format,
    ) {}

    public function map(string $value): \DateTimeInterface
    {
        $date = \DateTimeImmutable::createFromFormat($this->format, $value);

        if ($date === false) {
            throw new \My\App\CustomDateException("Invalid datetime value `$value`");
        }

        return $date;
    }
}

final readonly class User
{
    public string $name;

    #[\My\App\DateTimeFormat('Y-m-d')]
    public \DateTimeInterface $birthdate;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->filterExceptions(function (\Throwable $error) {
        if ($error instanceof \My\App\CustomDateException) {
            return \CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder::from($error);
        }

        throw $error;
    })
    ->mapper()
    ->map(\My\App\User::class, [
        'name' => 'John Doe',
        'birthdate' => '1971/11/08',
    ]);
```

[validation and error handling section]: ../usage/validation-and-error-handling.md
