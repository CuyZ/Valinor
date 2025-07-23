# Changelog 1.9.0 — 2nd of February 2024

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.9.0

## Notable changes

**JSON normalizer**

The normalizer is able to normalize a data structure to JSON without using the
native `json_encode()` function.

Using the normalizer instead of the native `json_encode()` function offers some
benefits:

- Values will be recursively normalized using the default transformations
- All registered transformers will be applied to the data before it is formatted
- The JSON can be streamed to a PHP resource in a memory-efficient way

Basic usage:

```php
namespace My\App;

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json());

$userAsJson = $normalizer->normalize(
    new \My\App\User(
        name: 'John Doe',
        age: 42,
        country: new \My\App\Country(
            name: 'France',
            code: 'FR',
        ),
    )
);

// `$userAsJson` is a valid JSON string representing the data:
// {"name":"John Doe","age":42,"country":{"name":"France","code":"FR"}}
```

By default, the JSON normalizer will return a JSON string representing the data
it was given. Instead of getting a string, it is possible to stream the JSON
data to a PHP resource:

```php
$file = fopen('path/to/some_file.json', 'w');

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->streamTo($file);

$normalizer->normalize(/* … */);

// The file now contains the JSON data
```

Another benefit of streaming the data to a PHP resource is that it may be more
memory-efficient when using generators — for instance when querying a database:

```php
// In this example, we assume that the result of the query below is a
// generator, every entry will be yielded one by one, instead of
// everything being loaded in memory at once.
$users = $database->execute('SELECT * FROM users');

$file = fopen('path/to/some_file.json', 'w');

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->streamTo($file);

// Even if there are thousands of users, memory usage will be kept low
// when writing JSON into the file.
$normalizer->normalize($users);
```

## Features

* Introduce JSON normalizer ([959740](https://github.com/CuyZ/Valinor/commit/9597407db04ff9d6e59ac8c6b8895d5fcf9caaa5))

## Bug Fixes

* Add default transformer for `DateTimeZone` ([acf097](https://github.com/CuyZ/Valinor/commit/acf0976ae0f63604fef28240531048a9f5633375))
* Detect circular references linearly through objects ([36aead](https://github.com/CuyZ/Valinor/commit/36aead96bd8b5696a321bc2df7386aa16264ab58))

## Other

* Refactor attribute definition to include class definition ([4b8cf6](https://github.com/CuyZ/Valinor/commit/4b8cf65080088c77c5257ae2e7da2d1fdee2fb2e))
