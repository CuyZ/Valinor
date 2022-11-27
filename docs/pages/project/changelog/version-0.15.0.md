# Changelog 0.15.0 — 6th of October 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.15.0

## Notable changes

Two similar features are introduced in this release: constants and enums 
wildcard notations. This is mainly useful when several cases of an enum or class
constants share a common prefix.

Example for class constants:

```php
final class SomeClassWithConstants
{
    public const FOO = 1337;

    public const BAR = 'bar';

    public const BAZ = 'baz';
}

$mapper = (new MapperBuilder())->mapper();

$mapper->map('SomeClassWithConstants::BA*', 1337); // error
$mapper->map('SomeClassWithConstants::BA*', 'bar'); // ok
$mapper->map('SomeClassWithConstants::BA*', 'baz'); // ok
```

Example for enum:

```php
enum SomeEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}

$mapper = (new MapperBuilder())->mapper();

$mapper->map('SomeEnum::BA*', 'foo'); // error
$mapper->map('SomeEnum::BA*', 'bar'); // ok
$mapper->map('SomeEnum::BA*', 'baz'); // ok
```

## Features

* Add support for class constant type ([1244c2](https://github.com/CuyZ/Valinor/commit/1244c2d68f3478560241e89f74f28b36d6fc2888))
* Add support for wildcard in enumeration type ([69ebd1](https://github.com/CuyZ/Valinor/commit/69ebd19ee84cc56cc8c986f1b5aff299e8d62b5c))
* Introduce utility class to build messages ([cb8792](https://github.com/CuyZ/Valinor/commit/cb87925aac45ca1babea3f34b6cd6e24c9172905))

## Bug Fixes

* Add return types for cache implementations ([0e8f12](https://github.com/CuyZ/Valinor/commit/0e8f12e5f7ffd2193c8ab772dd98d5e1cc858b59))
* Correctly handle type inferring during mapping ([37f96f](https://github.com/CuyZ/Valinor/commit/37f96f101d95e843f6a83d7e29c84b945938a691))
* Fetch correct node value for children ([3ee526](https://github.com/CuyZ/Valinor/commit/3ee526cb27816910f5bf27380021fa1399206335))
* Improve scalar values casting ([212b77](https://github.com/CuyZ/Valinor/commit/212b77fd13c7ead3ba7c0aca165ea3af235b1fa9))
* Properly handle static anonymous functions ([c009ab](https://github.com/CuyZ/Valinor/commit/c009ab98cc327c80e6589c22ee0b3c06b56849de))

## Other

* Import namespace token parser inside library ([0b8ca9](https://github.com/CuyZ/Valinor/commit/0b8ca98a2c9051213f3a59be513f16199288d45f))
* Remove unused code ([b2889a](https://github.com/CuyZ/Valinor/commit/b2889a3ba022410a1929c969acae8e581f670535), [de8aa9](https://github.com/CuyZ/Valinor/commit/de8aa9f4402c7e83a3575a6143eb26b45ea13461))
* Save type token symbols during lexing ([ad0f8f](https://github.com/CuyZ/Valinor/commit/ad0f8fee17773ec226bc4b1eb12370bfcd437187))
