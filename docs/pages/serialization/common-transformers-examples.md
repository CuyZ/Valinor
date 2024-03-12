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
- [Transforming objects](#transforming-objects)
- [Versioning API](#versioning-api)

## Customizing dates format

By default, dates will be formatted using the RFC 3339 format, but it may be
needed to use another format. 

This can be done on all dates, using a global transformer, as shown in the
example below:

<details>
<summary>Show code example — Global date format</summary>

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(
        fn (\DateTimeInterface $date) => $date->format('Y/m/d')
    )
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\Event(
            eventName: 'Release of legendary album',
            date: new \DateTimeImmutable('1971-11-08'),
        )
    );

// [
//     'eventName' => 'Release of legendary album',
//     'date' => '1971/11/08',
// ]
```
</details>

For a more granular control, an attribute can be used to target a specific
property, as shown in the example below:

<details>
<summary>Show code example — Date format attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Normalizer\AsTransformer]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class DateTimeFormat
{
    public function __construct(private string $format) {}

    public function normalize(\DateTimeInterface $date): string
    {
        return $date->format($this->format);
    }
}

final readonly class Event
{
    public function __construct(
        public string $eventName,
        #[\My\App\DateTimeFormat('Y/m/d')]
        public \DateTimeInterface $date,
    ) {}
}

(new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\Event(
            eventName: 'Release of legendary album',
            date: new \DateTimeImmutable('1971-11-08'),
        )
    );

// [
//     'eventName' => 'Release of legendary album',
//     'date' => '1971/11/08',
// ]
```
</details>

## Transforming property name to “snake_case”

Depending on the conventions of the data format, it may be necessary to
transform the case of the keys, for instance from “camelCase” to “snake_case”.

If this transformation is needed on every object, it can be done globally by
using a global transformer, as shown in the example below:

<details>
<summary>Show code example — global “snake_case” properties</summary>

```php
namespace My\App;

final class CamelToSnakeCaseTransformer
{
    public function __invoke(object $object, callable $next): mixed
    {
        $result = $next();

        if (! is_array($result)) {
            return $result;
        }

        $snakeCased = [];

        foreach ($result as $key => $value) {
            $newKey = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($key)));

            $snakeCased[$newKey] = $value;
        }

        return $snakeCased;
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(new \My\App\CamelToSnakeCaseTransformer())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
         new \My\App\User(
            name: 'John Doe',
            emailAddress: 'john.doe@example.com', 
            age: 42,
            country: new Country(
                name: 'France',
                countryCode: 'FR',
            ),
        )
    );

// [
//     'name' => 'John Doe',
//     'email_address' => 'john.doe@example', // snake_case
//     'age' => 42,
//     'country' => [
//         'name' => 'France',
//         'country_code' => 'FR', // snake_case
//     ],
// ]
```

</details>

For a more granular control, an attribute can be used to target specific
objects, as shown in the example below:

<details>
<summary>Show code example — “snake_case” attribute</summary>

```php
namespace My\App;

#[\CuyZ\Valinor\Normalizer\AsTransformer]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class SnakeCaseProperties
{
    public function normalize(object $object, callable $next): array
    {
        $result = $next();

        if (! is_array($result)) {
            return $result;
        }

        $snakeCased = [];

        foreach ($result as $key => $value) {
            $newKey = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($key)));

            $snakeCased[$newKey] = $value;
        }

        return $snakeCased;
    }
}

#[\My\App\SnakeCaseProperties]
final readonly class Country
{
    public function __construct(
        public string $name,
        public string $countryCode,
    ) {}
}

(new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\User(
            name: 'John Doe',
            emailAddress: 'john.doe@example.com',
            age: 42,
            country: new Country(
                name: 'France',
                countryCode: 'FR',
            ),
        )
    );

// [
//     'name' => 'John Doe',
//     'emailAddress' => 'john.doe@example', // camelCase
//     'age' => 42,
//     'country' => [
//         'name' => 'France',
//         'country_code' => 'FR', // snake_case
//     ],
// ]
```
</details>

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

(new \CuyZ\Valinor\MapperBuilder())
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

(new \CuyZ\Valinor\MapperBuilder())
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

(new \CuyZ\Valinor\MapperBuilder())
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
    return (new \CuyZ\Valinor\MapperBuilder())
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
