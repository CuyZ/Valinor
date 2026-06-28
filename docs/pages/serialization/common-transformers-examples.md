# Common transformers examples

Instead of providing transformers out-of-the-box, this library focuses on easing
the creation of custom ones. This way, the normalizer is not tied up to a
third-party library release-cycle and can be adapted to fit the needs of the
application's business logics.

Below is a list of common features that can inspire or be implemented by
third-party libraries or applications.

!!! info inline end
    These examples are not available out-of-the-box, they can be implemented
    using the library's API and should be adapted to fit the needs of the
    application.

- [Customizing dates format](#customizing-dates-format)
- [Transforming property name to “snake_case”](#transforming-property-name-to-snake_case)
- [Ignoring properties](#ignoring-properties)
- [Renaming properties](#renaming-properties)
- [Flattening single property objects](#flattening-single-property-objects)
- [Transforming objects](#transforming-objects)
- [Versioning API](#versioning-api)

## Customizing dates format

See [date time configurator chapter].

[date time configurator chapter]: use-provided-normalizer-configurators.md#specifying-date-time-normalization-format

## Transforming property name to “snake_case”

See [snake_case configurator chapter].

[snake_case configurator chapter]: use-provided-normalizer-configurators.md#converting-keys-to-snake_case

## Ignoring properties

Some objects might want to omit some properties during normalization, for
instance, to hide sensitive data.

In the example below, an attribute is added on a property that will replace the
value with a custom object that is afterward removed by a global transformer. 

<details>
<summary>Show code example — Ignore property attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Normalizer\AsTransformer]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Ignore
{
    public function normalize(mixed $value): IgnoredValue
    {
        return new \My\App\IgnoredValue();
    }
}

final class IgnoredValue
{
    public function __construct() {}
}

final readonly class User
{
    public function __construct(
        public string $name,
        #[\My\App\Ignore]
        public string $password,
    ) {}
}

(new \CuyZ\Valinor\NormalizerBuilder())
    ->registerTransformer(
        fn (object $value, callable $next) => array_filter(
            $next(),
            fn (mixed $value) => ! $value instanceof \My\App\IgnoredValue,
        ),
    )
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(new \My\App\User(
        name: 'John Doe',
        password: 's3cr3t-p4$$w0rd')
    );

// ['name' => 'John Doe']
```
</details>

## Renaming properties

Properties' names can differ between the object and the data format.

In the example below, an attribute is added on properties that need to be
renamed during normalization

<details>
<summary>Show code example — Rename property attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Normalizer\AsTransformer]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Rename
{
    public function __construct(private string $name) {}

    public function normalizeKey(): string
    {
        return $this->name;
    }
}

final readonly class Address
{
    public function __construct(
        public string $street,
        public string $zipCode,
        #[\My\App\Rename('town')]
        public string $city,
    ) {}
}

(new \CuyZ\Valinor\NormalizerBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new Address(
            street: '221B Baker Street', 
            zipCode: 'NW1 6XE', 
            city: 'London', 
        )
    );

// [
//     'street' => '221B Baker Street',
//     'zipCode' => 'NW1 6XE',
//     'town' => 'London',
// ]
```
</details>

## Flattening single property objects

When an object only has one property, it may be useful to flatten it so that
instead of `['someProperty' => 'value']` the normalized result is simply
`'value'`.

This transformation can be applied globally on all objects, as shown in the
example below:

<details>
<summary>Show code example — Global single property object flattening</summary>

```php
namespace My\App;

final class SinglePropertyObjectFlattener
{
    public function __invoke(object $object, callable $next): mixed
    {
        $result = $next();

        if (is_array($result) && count($result) === 1) {
            return current($result);
        }

        return $result;
    }
}

(new \CuyZ\Valinor\NormalizerBuilder())
    ->registerTransformer(new \My\App\SinglePropertyObjectFlattener())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
         new \My\App\Email(email: 'john.doe@example.com')
    );

// 'john.doe@example.com'
```

</details>

For a more granular control, an attribute can be used to target specific objects
or properties, as shown in the example below:

<details>
<summary>Show code example — Single property object flattening attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Normalizer\AsTransformer]
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
final class Flatten
{
    public function normalize(object $object, callable $next): mixed
    {
        $result = $next();

        if (is_array($result) && count($result) === 1) {
            return current($result);
        }

        return $result;
    }
}

final readonly class User
{
    public function __construct(
        public string $name,
        #[\My\App\Flatten]
        public \My\App\Email $email,
    ) {}
}

(new \CuyZ\Valinor\NormalizerBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new User(
            name: 'John Doe',
            email: new \My\App\Email('john.doe@example.com'),
        )
    );

// [
//     'name' => 'John Doe',
//     'email' => 'john.doe@example.com',
// ]
```
</details>

## Transforming objects

Some objects can have custom behaviors during normalization, for instance
properties may need to be remapped. In the example below, a transformer will
check if an object defines a `normalize` method and use it if it exists.

<details>
<summary>Show code example — Custom object normalization</summary>

```php
namespace My\App;

final readonly class Address
{
    public function __construct(
        public string $road,
        public string $zipCode,
        public string $town,
    ) {}

    public function normalize(): array
    {
        return [
            'street' => $this->road,
            'postalCode' => $this->zipCode,
            'city' => $this->town,
        ];
    }
}

(new \CuyZ\Valinor\NormalizerBuilder())
    ->registerTransformer(function (object $object, callable $next) {
        return method_exists($object, 'normalize')
            ? $object->normalize()
            : $next();
    })
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\Address(
            road: '221B Baker Street',
            zipCode: 'NW1 6XE',
            town: 'London',
        ),
    );

// [
//     'street' => '221B Baker Street',
//     'postalCode' => 'NW1 6XE',
//     'city' => 'London',
// ]
```
</details>

## Versioning API

API versioning can be implemented with different strategies and algorithms. The
example below shows how objects can implement an interface to specify their own
specific versioning behavior.

<details>
<summary>Show code example — Versioning objects</summary>

```php
namespace My\App;

interface HasVersionedNormalization
{
    public function normalizeWithVersion(string $version): mixed;
}

final readonly class Address implements \My\App\HasVersionedNormalization
{
    public function __construct(
        public string $streetNumber,
        public string $streetName,
        public string $zipCode,
        public string $city,
    ) {}

    public function normalizeWithVersion(string $version): array
    {
        return match (true) {
            version_compare($version, '1.0.0', '<') => [
                // Street number and name are merged in a single property
                'street' => "$this->streetNumber, $this->streetName",
                'zipCode' => $this->zipCode,
                'city' => $this->city,
            ],
            default => get_object_vars($this),
        };
    }
}

function normalizeWithVersion(string $version): mixed
{
    return (new \CuyZ\Valinor\NormalizerBuilder())
        ->registerTransformer(
            fn (\My\App\HasVersionedNormalization $object) => $object->normalizeWithVersion($version)
        )
        ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
        ->normalize(
            new \My\App\Address(
                streetNumber: '221B',
                streetName: 'Baker Street',
                zipCode: 'NW1 6XE',
                city: 'London',
            )
        );
}

// Version can come for instance from HTTP request headers
$result_v0_4 = normalizeWithVersion('0.4');
$result_v1_8 = normalizeWithVersion('1.8');

// $result_v0_4 === [
//     'street' => '221B, Baker Street',
//     'zipCode' => 'NW1 6XE',
//     'city' => 'London',
// ]
// 
// $result_v1_8 === [
//     'streetNumber' => '221B',
//     'streetName' => 'Baker Street',
//     'zipCode' => 'NW1 6XE',
//     'city' => 'London',
// ]
```
</details>
