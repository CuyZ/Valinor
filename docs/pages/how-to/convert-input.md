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

## Converting source keys

When the input data uses different key names than the PHP codebase, key
converters can be used to tell the mapper how to match source keys to object
properties or shaped array elements.

Unlike value converters, key converters do not transform the input data itself,
they only remap keys.

!!! note

    Error messages will reference the *original* source key names, so the end
    user sees the key that was actually sent.

```php
$mapper = (new \CuyZ\Valinor\MapperBuilder())
    ->registerKeyConverter(static function (string $key): string {
        // Strips the `billing_` prefix from source keys
        if (str_starts_with($key, 'billing_')) {
            return substr($key, 8);
        }

        return $key;
    })
    ->mapper();

final readonly class BillingAddress
{
    public function __construct(
        public string $street,
        public string $city,
        public string $country,
    ) {}
}

$source = [
    'billing_street' => '221B Baker Street',
    'billing_city' => 'London',
    'billing_country' => 'UK',
];

// Works with classes
$mapper->map(BillingAddress::class, $source);

// Also works with shaped arrays
$mapper->map('array{street: string, city: string, country: string}', $source);
```

### Chaining key converters

Multiple key converters can be registered and are applied as a pipeline; each
one transforms the result of the previous one, in registration order:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerKeyConverter(static function (string $key): string {
        // Strip the "billing_" prefix
        if (str_starts_with($key, 'billing_')) {
            return substr($key, 8);
        }

        return $key;
    })
    ->registerKeyConverter(
        // Replace hyphens with underscores
        static fn (string $key): string => str_replace('-', '_', $key),
    )
    ->mapper()
    ->map('array{zip_code: string, country_name: string}', [
        'billing_zip-code' => '62701',
        'billing_country-name' => 'United Kingdom',
    ]);
```

## Converters error handling

When a value converter or a key converter throws an exception, the mapper will
properly handle it only if it follows the rules defined in the [validation and
error handling section].

### Value converter errors

```php
namespace My\App;

final class InvalidPriceException extends \RuntimeException { }

#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class PriceInCents
{
    public function map(string $value): int
    {
        if (preg_match('/^\d+(\.\d{2})?$/', $value) !== 1) {
            throw new \My\App\InvalidPriceException(
                "Invalid price format `$value`"
            );
        }

        return (int)round((float)$value * 100);
    }
}

final readonly class Product
{
    public string $name;

    #[\My\App\PriceInCents]
    public int $priceInCents;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->filterExceptions(function (\Throwable $error) {
        if ($error instanceof \My\App\InvalidPriceException) {
            return \CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder::from($error);
        }

        throw $error;
    })
    ->mapper()
    ->map(\My\App\Product::class, [
        'name' => 'Widget',
        'priceInCents' => 'not-a-price',
    ]);

// Invalid price format `not-a-price`
```

### Key converter errors

Key converters follow the same error handling rules. When a key converter throws
an exception, it is caught and reported against the original source key.

```php
namespace My\App;

final class ForbiddenKeyException extends \RuntimeException { }

(new \CuyZ\Valinor\MapperBuilder())
    ->registerKeyConverter(static function (string $key): string {
        if (str_starts_with($key, '__')) {
            throw new \My\App\ForbiddenKeyException(
                "Key `$key` is not allowed"
            );
        }

        return strtolower($key);
    })
    ->filterExceptions(function (\Throwable $error) {
        if ($error instanceof \My\App\ForbiddenKeyException) {
            return \CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder::from($error);
        }

        throw $error;
    })
    ->mapper()
    ->map('array{name: string}', [
        '__forbidden' => 'hello',
    ]);
    
// Key `__forbidden` is not allowed
```

[validation and error handling section]: ../usage/validation-and-error-handling.md
