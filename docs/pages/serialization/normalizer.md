# Normalizing data

A normalizer is a service that transforms a given input into scalar and array
values, while preserving the original structure.

This feature can be used to share information with other systems that use a data
format (JSON, CSV, XML, etc.). The normalizer will take care of recursively
transforming the data into a format that can be serialized.

!!! info
    The library only supports normalizing to arrays, but aims to support other
    formats like JSON or CSV in the future.

    In the meantime, native functions like `json_encode()` may be used as an
    alternative.

## Basic usage

```php
namespace My\App;

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array());

$userAsArray = $normalizer->normalize(
    new \My\App\User(
        name: 'John Doe',
        age: 42,
        country: new Country(
            name: 'France',
            countryCode: 'FR',
        ),
    )
);

// `$userAsArray` is now an array and can be manipulated much more easily, for
// instance to be serialized to the wanted data format.
//
// [
//     'name' => 'John Doe',
//     'age' => 42,
//     'country' => [
//         'name' => 'France',
//         'countryCode' => 'FR',
//     ],
// ];
```

## Extending the normalizer

This library provides a normalizer out-of-the-box that can be used as-is, or
extended to add custom logic. To do so, transformers must be registered within
the `MapperBuilder`.

A transformer can be a callable (function, closure or a class implementing the
`__invoke()` method), or an attribute that can target a class or a property.

!!! note
    You can find common examples of transformers in the [next
    chapter](common-examples.md).

### Callable transformers

A callable transformer must declare at least one argument, for which the type
will determine when it is used during normalization. For instance, when a string
is found during normalization, all transformers that have a first parameter with
a `string` type will be called.

Transformers can be chained. To do so, a second parameter of type `callable`
must be declared in a transformer. This parameter — named `$next` by convention
— can be used whenever needed in the transformer logic.

```php
(new \CuyZ\Valinor\MapperBuilder())

    // The type of the first parameter of the transformer will determine when it
    // is used during normalization. Note that advanced type annotations
    // like `non-empty-string` can be used to target a more specific type.
    ->registerTransformer(
        /**
         * @param non-empty-string $value 
         */
        fn (string $value, callable $next) => strtoupper($next())
    )

    // Transformers can be chained, the last registered one will take precedence
    // over the previous ones, which can be called using the `$next` parameter.
    ->registerTransformer(
        fn (string $value, callable $next) => $next() . '!'
    )

    // A priority can be given to a transformer, to make sure it is called
    // before or after another one. The higher the priority, the sooner the
    // transformer will be called. Default priority is 0.
    ->registerTransformer(
        fn (string $value, callable $next) => $next() . '?',
        priority: -100
    )

    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize('Hello world'); // HELLO WORLD?!
```

### Attribute transformers

Callable transformers allow targeting any value during normalization, whereas
attribute transformers allow targeting a specific class or property for a more
granular control.

To be detected by the normalizer, an attribute must be registered first by
giving its class name to the `registerTransformer` method.

!!! tip
    It is possible to register attributes that share a common interface by
    giving the interface name to the method.

    ```php
    namespace My\App;

    interface SomeAttributeInterface {}

    #[\Attribute]
    final class SomeAttribute implements SomeAttributeInterface {}

    #[\Attribute]
    final class SomeOtherAttribute implements SomeAttributeInterface {}

    (new \CuyZ\Valinor\MapperBuilder())
        // Registers both `SomeAttribute` and `SomeOtherAttribute` attributes
        ->registerTransformer(\My\App\SomeAttributeInterface::class)
        …
    ```

Attributes must declare a method named `normalize` that follows the same rules
as callable transformers: a mandatory first parameter and an optional second
`callable` parameter.

```php
namespace My\App;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Uppercase
{
    public function normalize(string $value, callable $next): string
    {
        return strtoupper($next());
    }
}

final readonly class City
{
    public function __construct(
        public string $zipCode,
        #[Uppercase]
        public string $name,
        #[Uppercase]
        public string $country,
    ) {}
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(Uppercase::class)
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\City(
            zipCode: 'NW1 6XE',
            name: 'London',
            country: 'United Kingdom',
        ) 
    );

// [
//     'zipCode' => 'NW1 6XE',
//     'name' => 'LONDON',
//     'country' => 'UNITED KINGDOM',
// ]
```

If an attribute needs to transform the key of a property, it needs to declare a
method named `normalizeKey`.

```php
namespace My\App;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class PrefixedWith
{
    public function __construct(private string $prefix) {}

    public function normalizeKey(string $value): string
    {
        return $this->prefix . $value;
    }
}

final readonly class Address
{
    public function __construct(
        #[\My\App\PrefixedWith('address_')]
        public string $road,
        #[\My\App\PrefixedWith('address_')]
        public string $zipCode,
        #[\My\App\PrefixedWith('address_')]
        public string $city,
    ) {}
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(PrefixedWith::class)
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\Address(
            road: '221B Baker Street',
            zipCode: 'NW1 6XE',
            city: 'London',
        ) 
    );

// [
//     'address_road' => '221B Baker Street',
//     'address_zipCode' => 'NW1 6XE',
//     'address_city' => 'London',
// ]
```
