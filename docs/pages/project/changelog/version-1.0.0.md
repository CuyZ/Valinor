# Changelog 1.0.0 — 28th of November 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.0.0

First stable version! 🥳 🎉

This release marks the end of the initial development phase. The library has 
been live for [exactly one year] at this date and is stable enough to start 
following the [semantic versioning] — it means that any backward incompatible 
change (aka breaking change) will lead to a bump of the major version.

This is the biggest milestone achieved by this project (yet™); I want to thank
everyone who has been involved to make it possible, especially the 
[contributors] who submitted high-quality pull requests to improve the library.

There is also one person that I want to thank even more: my best friend
[Nathan](https://github.com/Mopolo/), who has always been so supportive with my
side-projects. Thanks, bro! 🙌

The last year marked a bigger investment of my time in OSS contributions; I've
proven to myself that I am able to follow a stable way of managing my engagement
to this community, and this is why I enabled sponsorship on my profile to allow
people to **❤️ [sponsor my work on GitHub]** — if you use this library in your
applications, please consider offering me a 🍺 from time to time! 🤗

[exactly one year]: https://github.com/CuyZ/Valinor/commit/396f64a5246ccfe3f6f6d3211bac7f542a9c7fc6
[semantic versioning]: https://semver.org
[sponsor my work on GitHub]: https://github.com/sponsors/romm
[contributors]: https://github.com/CuyZ/Valinor/graphs/contributors

## Notable changes

**End of PHP 7.4 support**

PHP 7.4 security support [has ended on the 28th of November
2022](https://www.php.net/supported-versions.php); the minimum version supported
by this library is now PHP 8.0.

**New mapper to map arguments of a callable**

This new mapper can be used to ensure a source has the right shape before
calling a function/method.

The mapper builder can be configured the same way it would be with a tree
mapper, for instance to customize the type strictness.

```php
$someFunction = function(string $foo, int $bar): string {
    return "$foo / $bar";
};

try {
    $arguments = (new \CuyZ\Valinor\MapperBuilder())
        ->argumentsMapper()
        ->mapArguments($someFunction, [
            'foo' => 'some value',
            'bar' => 42,
        ]);

    // some value / 42
    echo $someFunction(...$arguments);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Do something…
}
```

**Support for `TimeZone` objects**

Native `TimeZone` objects construction is now supported with a proper error
handling.

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(DateTimeZone::class, 'Jupiter/Europa');
} catch (MappingError $exception) {
    $error = $exception->node()->messages()[0];

    // Value 'Jupiter/Europa' is not a valid timezone.
    echo $error->toString();
}
```

**Mapping object with one property**

When a class needs only one value, the source given to the mapper must match the
type of the single property/parameter.

This change aims to bring consistency on how the mapper behaves when mapping an
object that needs one argument. Before this change, the source could either
match the needed type, or be an array with a single entry and a key named after
the argument.

See example below:

```php
final class Identifier
{
    public readonly string $value;
}

final class SomeClass
{
    public readonly Identifier $identifier;

    public readonly string $description;
}

(new \CuyZ\Valinor\MapperBuilder())->mapper()->map(SomeClass::class, [
    'identifier' => ['value' => 'some-identifier'], // ❌
    'description' => 'Lorem ipsum…',
]);

(new \CuyZ\Valinor\MapperBuilder())->mapper()->map(SomeClass::class, [
    'identifier' => 'some-identifier', // ✅
    'description' => 'Lorem ipsum…',
]);
```

## Upgrading from 0.x to 1.0

As this is a major release, all deprecated features have been removed, leading
to an important number of breaking changes.

You can click on the entries below to get advice on available replacements. 

??? tip "Doctrine annotations support removal"

    Doctrine annotations cannot be used anymore, [PHP attributes] must be used.

??? tip "`BackwardCompatibilityDateTimeConstructor` class removal"

    You must use the method available in the mapper builder, see [dealing 
    with dates chapter].

??? tip "Mapper builder `flexible` method removal"

    The flexible has been split in three disctint modes, see [type strictness
    & flexibility chapter].

??? tip "Mapper builder `withCacheDir` method removal"

    You must now register a cache instance directly, see [performance & 
    caching chapter].

??? tip "`StaticMethodConstructor` class removal"

    You must now register the constructors using the mapper builder, see [custom
    object constructors chapter].

??? tip "Mapper builder `bind` method removal"

    You must now register the constructors using the mapper builder, see [custom
    object constructors chapter].

??? tip "`ThrowableMessage` class removal"

    You must now use the `MessageBuilder` class, see [error handling chapter].

??? tip "`MessagesFlattener` class removal"

    You must now use the `Messages` class, see [error handling chapter].

??? tip "`TranslatableMessage` class removal"

    You must now use the `HasParameters` class, see [custom exception chapter].

??? tip "Message methods removal"

    The following methods have been removed:

    - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::name()`
    - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::path()`
    - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::type()`
    - `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::value()`
    - `\CuyZ\Valinor\Mapper\Tree\Node::value()`

    It is still possible to get the wanted values using the method
    `\CuyZ\Valinor\Mapper\Tree\Message\NodeMessage::node()`.

    The placeholder `{original_value}` has also been removed, the same value can
    be fetched with `{source_value}`.

??? tip "`PlaceHolderMessageFormatter` class removal"

    Other features are available to format message, see [error messages
    customization chapter].

??? tip "`Identifier` attribute removal"

    This feature has been part of the library since its first public release,
    but it was never documented because it did not fit one of the library's main 
    philosophy which is to be almost entirely decoupled from an application's
    domain layer.
    
    The feature is entirely removed and not planned to be replaced by an
    alternative, unless the community really feels like there is a need for
    something alike.

[PHP attributes]: https://www.php.net/manual/en/language.attributes.overview.php
[dealing with dates chapter]: ../../how-to/deal-with-dates.md
[type strictness & flexibility chapter]: ../../usage/type-strictness-and-flexibility.md
[performance & caching chapter]: ../../other/performance-and-caching.md
[custom object constructors chapter]: ../../how-to/use-custom-object-constructors.md 
[error handling chapter]: ../../usage/validation-and-error-handling.md
[custom exception chapter]: ../../usage/validation-and-error-handling.md#custom-exception-messages
[error messages customization chapter]: ../../how-to/customize-error-messages.md

## ⚠ BREAKING CHANGES

* Disallow array when mapping to object with one argument ([72cba3](https://github.com/CuyZ/Valinor/commit/72cba320f582c7cda63865880a1cbf7ea292d2b1))
* Mark tree mapper and arguments mapper as `@pure` ([0d9855](https://github.com/CuyZ/Valinor/commit/0d98555b8289248e84c55873bca7bca6968fc6e0))
* Remove deprecated backward compatibility datetime constructor ([a65e8d](https://github.com/CuyZ/Valinor/commit/a65e8d91f65004ffefe087f4c7b024678f739e83))
* Remove deprecated class `ThrowableMessage` ([d36ca9](https://github.com/CuyZ/Valinor/commit/d36ca9887e0a94d7244fba2df7aa7e24d6b11f8d))
* Remove deprecated class to flatten messages ([f9ed93](https://github.com/CuyZ/Valinor/commit/f9ed93e98c09c7a6120c0911e5e5d598eb86a417))
* Remove deprecated interface `TranslatableMessage` ([ceb197](https://github.com/CuyZ/Valinor/commit/ceb19729c9a683e4f477857916fcdce7256830ee))
* Remove deprecated message methods ([e6557d](https://github.com/CuyZ/Valinor/commit/e6557dde5255857526c637fab2b2757030a6a413))
* Remove deprecated method constructor attribute ([d76467](https://github.com/CuyZ/Valinor/commit/d76467194c43c28912e1af3d85a92308e90db64e))
* Remove deprecated method to enable flexible mode ([a2bef3](https://github.com/CuyZ/Valinor/commit/a2bef3497a535dae78dfb95614aaa2947256380f))
* Remove deprecated method to set cache directory ([b0d6d2](https://github.com/CuyZ/Valinor/commit/b0d6d2fc7ddb2cccc99fa182daaa2a980bde7338))
* Remove deprecated method used to bind a callback ([b79ed8](https://github.com/CuyZ/Valinor/commit/b79ed81253fc8df758e935772fbd0800c3f41317))
* Remove deprecated placeholder message formatter ([c2723d](https://github.com/CuyZ/Valinor/commit/c2723dac0bf8063dacd7e30b91dbe9f684addbf8))
* Remove Doctrine annotations support ([66c182](https://github.com/CuyZ/Valinor/commit/66c1829fcb7b7942947fcbfb11d4b1e4d1cfea15))
* Remove identifier attribute ([8a7486](https://github.com/CuyZ/Valinor/commit/8a7486aa440aa8068a43fe5b1e3731ce880b9daf))
* Remove PHP 7.4 support ([5f5a50](https://github.com/CuyZ/Valinor/commit/5f5a50123be5d5e9f5ebc9ca179bef8a2d6e1a4c))
* Remove support for `strict-array` type ([22c3b4](https://github.com/CuyZ/Valinor/commit/22c3b4fbaba05b05d834cd0492ede6f484b610ee))

## Features

* Add constructor for `DateTimeZone` with error support ([a0a4d6](https://github.com/CuyZ/Valinor/commit/a0a4d63d814a95874d7b7a5410d393bbd95329b7))
* Introduce mapper to map arguments of a callable ([9c7e88](https://github.com/CuyZ/Valinor/commit/9c7e884f13f51842cd038faf61a71467c8d25816))

## Bug Fixes

* Allow mapping `null` to single node nullable type ([0a98ec](https://github.com/CuyZ/Valinor/commit/0a98ec25122a629bd03cd7e71e0d407cd850fb28))
* Handle single argument mapper properly ([d7bf6a](https://github.com/CuyZ/Valinor/commit/d7bf6ab7a9e1e2266d3a9a4755c5042456cbb20a))
* Handle tree mapper call without argument in PHPStan extension ([3f3a01](https://github.com/CuyZ/Valinor/commit/3f3a0173fa33c3796f80e317aa3fbb0d1255e8d1))
* Handle tree mapper call without argument in Psalm plugin ([b425af](https://github.com/CuyZ/Valinor/commit/b425af7ae9511334a536c8ce5919d00c444fa36d))

## Other

* Activate value altering feature only when callbacks are registered ([0f33a5](https://github.com/CuyZ/Valinor/commit/0f33a5b4f34b02e8e5196d374918af60cee75438))
* Bump `psr/simple-cache` supported version ([e4059a](https://github.com/CuyZ/Valinor/commit/e4059a1cc8b7b639a274d5580382090a92a4a911))
* Remove `@` from comments for future PHP versions changes ([68774c](https://github.com/CuyZ/Valinor/commit/68774c778a7aed939b20314114e50b14b781968d))
* Update dependencies ([4afcda](https://github.com/CuyZ/Valinor/commit/4afcda966bd2e8aae8e2c8004156b3aef9add6a7))
