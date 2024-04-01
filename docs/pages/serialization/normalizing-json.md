# Normalizing to JSON

The normalizer is able to normalize a data structure to JSON without using the
native `json_encode()` function.

Using the normalizer instead of the native `json_encode()` function offers some
benefits:

- Values will be recursively normalized using the [default transformations] 
- All [registered transformers] will be applied to the data before it is
  formatted
- The JSON [can be streamed to a PHP resource] in a memory-efficient way

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
            countryCode: 'FR',
        ),
    )
);

// `$userAsJson` is a valid JSON string representing the data:
// {"name":"John Doe","age":42,"country":{"name":"France","countryCode":"FR"}}
```

## Streaming to a PHP resource

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
// In this example, we assume that the result of the query below is a generator,
// every entry will be yielded one by one, instead of everything being loaded in
// memory at once.
$users = $database->execute('SELECT * FROM users');

$file = fopen('path/to/some_file.json', 'w');

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->streamTo($file);

// Even if there are thousands of users, memory usage will be kept low when
// writing JSON into the file.
$normalizer->normalize($users);
```

## Passing `json_encode` flags

By default, the JSON normalizer will only use `JSON_THROW_ON_ERROR` to encode
non-boolean scalar values. There might be use-cases where projects will need
flags like `JSON_JSON_PRESERVE_ZERO_FRACTION`.

This can be achieved by passing these flags to the
`JsonNormalizer::withOptions()` method:

```php
namespace My\App;

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->withOptions(\JSON_PRESERVE_ZERO_FRACTION);

$lowerManhattanAsJson = $normalizer->normalize(
    new \My\App\Coordinates(
        longitude: 40.7128,
        latitude: -74.0000
    )
);

// `$lowerManhattanAsJson` is a valid JSON string representing the data:
// {"longitude":"40.7128","latitude":-74.0000}
```

The method accepts an int-mask of the following `JSON_*` constant
representations ([see official doc for more information]):

- `JSON_HEX_QUOT`
- `JSON_HEX_TAG`
- `JSON_HEX_AMP`
- `JSON_HEX_APOS`
- `JSON_INVALID_UTF8_IGNORE`
- `JSON_INVALID_UTF8_SUBSTITUTE`
- `JSON_NUMERIC_CHECK`
- `JSON_PRESERVE_ZERO_FRACTION`
- `JSON_UNESCAPED_LINE_TERMINATORS`
- `JSON_UNESCAPED_SLASHES`
- `JSON_UNESCAPED_UNICODE`

`JSON_THROW_ON_ERROR` is always enforced and thus is not accepted.

[default transformations]: normalizer.md#supported-transformations

[registered transformers]: extending-normalizer.md

[can be streamed to a PHP resource]: #streaming-to-a-php-resource

[see official doc for more information]: https://www.php.net/manual/en/json.constants.php
