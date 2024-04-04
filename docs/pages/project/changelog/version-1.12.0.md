# Changelog 1.12.0 — 4th of April 2024

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.12.0

## Notable changes

**Introduce unsealed shaped array syntax**

This syntax enables an extension of the shaped array type by allowing additional
values that must respect a certain type.

```php
$mapper = (new \CuyZ\Valinor\MapperBuilder())->mapper();

// Default syntax can be used like this:
$mapper->map(
    'array{foo: string, ...array<string>}',
    [
        'foo' => 'foo',
        'bar' => 'bar', // ✅ valid additional value
    ]
);

$mapper->map(
    'array{foo: string, ...array<string>}',
    [
        'foo' => 'foo',
        'bar' => 1337, // ❌ invalid value 1337
    ]
);

// Key type can be added as well:
$mapper->map(
    'array{foo: string, ...array<int, string>}',
    [
        'foo' => 'foo',
        42 => 'bar', // ✅ valid additional key
    ]
);

$mapper->map(
    'array{foo: string, ...array<int, string>}',
    [
        'foo' => 'foo',
        'bar' => 'bar' // ❌ invalid key
    ]
);

// Advanced types can be used:
$mapper->map(
    "array{
        'en_US': non-empty-string,
        ...array<non-empty-string, non-empty-string>
    }",
    [
        'en_US' => 'Hello',
        'fr_FR' => 'Salut', // ✅ valid additional value
    ]
);

$mapper->map(
    "array{
        'en_US': non-empty-string,
        ...array<non-empty-string, non-empty-string>
    }",
    [
        'en_US' => 'Hello',
        'fr_FR' => '', // ❌ invalid value
    ]
);

// If the permissive type is enabled, the following will work:
(new \CuyZ\Valinor\MapperBuilder())
    ->allowPermissiveTypes()
    ->mapper()
    ->map(
        'array{foo: string, ...}',
        ['foo' => 'foo', 'bar' => 'bar', 42 => 1337]
    ); // ✅
```

**Interface constructor registration**

By default, the mapper cannot instantiate an interface, as it does not know
which implementation to use. To do so, the `MapperBuilder::infer()` method can
be used, but it is cumbersome in most cases.

It is now also possible to register a constructor for an interface, in the same
way as for a class.

Because the mapper cannot automatically guess which implementation can be used 
for an interface, it is not possible to use the `Constructor` attribute, the
`MapperBuilder::registerConstructor()` method must be used instead.

In the example below, the mapper is taught how to instantiate an implementation
of `UuidInterface` from package `ramsey/uuid`:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        // The static method below has return type `UuidInterface`;
        // therefore, the mapper will build an instance of `Uuid` when
        // it needs to instantiate an implementation of `UuidInterface`.
        Ramsey\Uuid\Uuid::fromString(...)
    )
    ->mapper()
    ->map(
        Ramsey\Uuid\UuidInterface::class,
        '663bafbf-c3b5-4336-b27f-1796be8554e0'
    );
```

**JSON normalizer formatting options**

By default, the JSON normalizer will only use `JSON_THROW_ON_ERROR` to encode
non-boolean scalar values. There might be use-cases where projects will need
flags like `JSON_JSON_PRESERVE_ZERO_FRACTION`.

This can be achieved by passing these flags to the new
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
// {"longitude":40.7128,"latitude":-74.0000}
```

The method accepts an int-mask of the following `JSON_*` constant
representations:

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

See official doc for more information:
https://www.php.net/manual/en/json.constants.php

### Features

* Allow JSON normalizer to set JSON formatting options ([cd5df9](https://github.com/CuyZ/Valinor/commit/cd5df97d45b2687b4a79bf01e4b03d7deee28dfa))
* Allow mapping to `array-key` type ([5020d6](https://github.com/CuyZ/Valinor/commit/5020d62e00e00fdb74ac26e83dd36b313e0a5ee1))
* Handle interface constructor registration ([13f69a](https://github.com/CuyZ/Valinor/commit/13f69a9e1b5ce7f7a2305933764a928adf08c7df))
* Handle type importation from interface ([3af22d](https://github.com/CuyZ/Valinor/commit/3af22d16f6a4034611859a5998e6d0317d61dc4f))
* Introduce unsealed shaped array syntax ([fa8bb0](https://github.com/CuyZ/Valinor/commit/fa8bb0020c8792a17eadc9df0559d44f908b5397))

### Bug Fixes

* Handle class tokens only when needed during lexing ([c4be75](https://github.com/CuyZ/Valinor/commit/c4be75844bc71912fdbf34fac2523ca184d3c15f))
* Load needed information only during interface inferring ([c8e204](https://github.com/CuyZ/Valinor/commit/c8e204a4a3cdfc681e99f80e0c1632e663a32161))

### Other

* Rename internal class ([4c62d8](https://github.com/CuyZ/Valinor/commit/4c62d87a68a7de4e60d070cb7298a19fd7ebad5a))
