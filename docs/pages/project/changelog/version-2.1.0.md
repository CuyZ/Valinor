# Changelog 2.1.0 — 23rd of July 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.1.0

## Notable changes

**Attribute converters**

!!! info

    Fetch common examples of mapping converters [in the documentation].

    [in the documentation]: ../../how-to/common-converters-examples.md

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

final class User
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

### Features

* Introduce attribute converters for granular control during mapping ([0a8c0d](https://github.com/CuyZ/Valinor/commit/0a8c0d0574690ebf70e3959a2438dcf83ce58e5c))

### Bug Fixes

* Properly detect invalid values returned by mapping converters ([e80de7](https://github.com/CuyZ/Valinor/commit/e80de79deca89ed0149f439103f9af74fcbedea8))
* Properly extract `=` token when reading types ([9a511d](https://github.com/CuyZ/Valinor/commit/9a511d0e6693cfcd41e804b08826ec2be51d8433))
* Use polyfill for `array_find` ([540741](https://github.com/CuyZ/Valinor/commit/5407419b1a02806f9dd35a1ece025aece878220d))

### Other

* Mark exception as `@internal` ([f3eace](https://github.com/CuyZ/Valinor/commit/f3eacec680182fa3033acd92a63eb14d03be471b))
