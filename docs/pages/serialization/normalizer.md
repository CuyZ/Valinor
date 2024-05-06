# Normalizing data

A normalizer is a service that transforms a given input into scalar and array
values, while preserving the original structure.

This feature can be used to share information with other systems that use a data
format (JSON, CSV, XML, etc.). The normalizer will take care of recursively
transforming the data into a format that can be serialized.

Basic usage:

```php
namespace My\App;

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array());

$userAsArray = $normalizer->normalize(
    new \My\App\User(
        name: 'John Doe',
        age: 42,
        country: new \My\App\Country(
            name: 'France',
            countryCode: 'FR',
        ),
    ),
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

The normalizer accepts a data format so that it can directly convert the data to
the desired configuration, see [chapter about JSON normalization] for more
information.

## Supported transformations

The normalizer natively provides transformations for some types that may be
unsupported by serialization functions like `json_encode()`:

- Objects are transformed to arrays based on their properties, no matter their
  visibility (public, protected or private)
- Dates are formatted to the RFC 3339 format
- Backed enums use their value, unit enums use their name

Custom transformations can be applied, see [normalizer extension chapter] for
more information.

[chapter about JSON normalization]: normalizing-json.md

[normalizer extension chapter]: extending-normalizer.md
