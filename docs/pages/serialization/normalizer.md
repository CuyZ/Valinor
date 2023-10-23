# Normalizing data

A normalizer is a service that transforms a given input so that it contains only
scalar and array values, while preserving the original structure. This feature
is useful when an object must be serialized to a data format (JSON, CSV, XML,
etc.), for instance to share data with other systems.

Below is an example to help understanding how it works:

```php
$normalizer = (new \CuyZ\Valinor\MapperBuilder())->normalizer();

$userAsArray = $normalizer->normalize(
    new User(
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

This library provides a normalizer out-of-the-box, that can be used as-is, or
extended to add custom logic. To do so, transformers must be registered within
the `MapperBuilder`:

```php
(new \CuyZ\Valinor\MapperBuilder())

    // The type of the first parameter of the transformer will determine when it
    // will be used during normalization. Note that advanced type annotations
    // like `non-empty-string` can be used to target a more specific type.
    ->registerTransformer(
        fn (string $value, callable $next) => strtoupper($next())
    )

    // Transformers can be chained, the last registered one will take precedence
    // over the previous ones, which can be called using the `$next` parameter.
    ->registerTransformer(
        fn (string $value, callable $next) => $next() . '!'
    )

    // A priority can be given to a transformer, to make sure it is called
    // before or after another one. The higher the priority, the sooner the
    //transformer will be called. Default priority is 0.
    ->registerTransformer(
        fn (string $value, callable $next) => $next() . '?',
        priority: -100 // Negative priority: transformer will be called early. 
    )

    ->normalizer()
    ->normalize('Hello world'); // HELLO WORLD?!
```

## Common transformers examples

Below is a list of common features that can be implemented by third party
libraries or applications. Note that these are examples that can be adapted to
fit the real needs.

### Transforming to snake_case️ keys

Depending on the conventions of the data format, it may be necessary to
transform the case of the keys, for instance from camelCase to snake_case. In
the example below, the transformation is done globally and recursively on every
object during normalization.

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
    ->normalizer()
    ->normalize(
         new User(
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
//    'name' => 'John Doe',
//    'email_address' => 'john.doe@example',
//    'age' => 42,
//    'country' => [
//        'name' => 'France',
//        'country_code' => 'FR',
//    ],
// ]
```

### Customizing dates format

By default, dates will be formatted using the RFC 3339 format, but it is
possible to use another format by registering a transformer. 

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(
        fn (DateTimeInterface $date) => $date->format('Y/m/d')
    )
    ->normalizer()
    ->normalize(new DateTimeImmutable('1971-11-08'));

// 1971/11/08
```

### Transforming objects

Some objects can have custom behaviors during normalization, for instance
property names can be remapped. In the example below, a transformer will check
if an object has a specific method and use it if it exists.

```php
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
    ->normalizer()
    ->normalize(
        new Address(
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

### Ignoring properties

Some objects might want to omit some properties during normalization, for
instance to hide sensitive data. In the example below, an interface can be
implemented by objects to specify the properties that should be ignored.

```php
namespace My\App;

interface IgnoresValuesOnNormalization
{
    /**
     * @return non-empty-list<non-empty-string>
     */
    public function ignoredKeys(): array;
}

final readonly class User implements \My\App\IgnoresValuesOnNormalization
{
    public function __construct(
        public string $name,
        public string $password,
    ) {}

    public function ignoredKeys(): array
    {
        return ['password']; // The `password` property will be ignored on normalization
    }
}

final class IgnoredValuesTransformer
{
    function __invoke(\My\App\IgnoresValuesOnNormalization $object, callable $next): mixed
    {
        $result = $next();

        foreach ($object->ignoredKeys() as $key) {
            unset($result[$key]);
        }

        return $result;
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(new \My\App\IgnoredValuesTransformer())
    ->normalizer()
    ->normalize(new User(name: 'john.doe', password: 's3cr3t-p4$$w0rd'));

// ['name' => 'john.doe']
```

### Adding prefix to property name

Property names can differ between the object and the data format, for instance
a prefix can be added. In the example below, an interface can be implemented by
objects to specify the prefix to add.

```php
namespace My\App;

interface AddsPrefixOnNormalization
{
    public function prefix(): string;
}

final readonly class Address implements \My\App\AddsPrefixOnNormalization
{
    public function __construct(
        public string $street,
        public string $zipCode,
        public string $city,
    ) {}

    public function prefix(): string
    {
        return 'address_';
    }
}

final class PrefixedValuesHandler
{
    function __invoke(\My\App\AddsPrefixOnNormalization $object, callable $next): array
    {
        $prefixed = [];

        foreach ($next() as $key => $value) {
            $prefixed[$object->prefix() . $key] = $value;
        }

        return $prefixed;
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(new \My\App\PrefixedValuesHandler())
    ->normalizer()
    ->normalize(
        new Address(
            street: '221B Baker Street', 
            zipCode: 'NW1 6XE', 
            city: 'London', 
        )
    );

// [
//     'address_street' => '221B Baker Street',
//     'address_zipCode' => 'NW1 6XE',
//     'address_city' => 'London',
// ]
```

### Versioning API

API versioning can be implemented with different strategies and algorithms. The
example below shows how objects can implement an interface to specify their own
specific versioning behavior.

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
                'city' => $this->zipCode,
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
        ->normalizer()
        ->normalize(
            new Address(
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
