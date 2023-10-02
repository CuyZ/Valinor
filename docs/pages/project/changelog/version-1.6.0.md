# Changelog 1.6.0 — 25th of August 2023

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.6.0

## Notable changes

**Symfony Bundle**

A bundle is now available for Symfony applications, it will ease the integration
and usage of the Valinor library in the framework. The documentation can be 
found in the [CuyZ/Valinor-Bundle] repository.

Note that [the documentation has been updated] to add information about the bundle
as well as tips on how to integrate the library in other frameworks.

[the documentation has been updated]: https://valinor.cuyz.io/1.6/other/app-and-framework-integration/
[CuyZ/Valinor-Bundle]: https://github.com/CuyZ/Valinor-Bundle/#readme

**PHP 8.3 support**

Thanks to [@TimWolla], the library now supports PHP 8.3, which entered its beta
phase. Do not hesitate to test the library with this new version, and [report any
encountered issue on the repository].

**Better type parsing**

The first layer of the type parser has been completely rewritten. The previous
one would use regex to split a raw type in tokens, but that led to limitations —
mostly concerning quoted strings — that are now fixed.

Although this change should not impact the end user, it is a major change in the
library, and it is possible that some edge cases were not covered by tests. If
that happens, please [report any encountered issue on the repository].

Example of previous limitations, now solved:

```php
// Union of strings containing space chars
(new MapperBuilder())
    ->mapper()
    ->map(
        "'foo bar'|'baz fiz'",
        'baz fiz'
    );

// Shaped array with special chars in the key
(new MapperBuilder())
    ->mapper()
    ->map(
        "array{'some & key': string}",
        ['some & key' => 'value']
    );
```

**More advanced array-key handling**

It is now possible to use any string or integer as an array key. The following
types are now accepted and will work properly with the mapper:

```php
$mapper->map("array<'foo'|'bar', string>", ['foo' => 'foo']);

$mapper->map('array<42|1337, string>', [42 => 'foo']);

$mapper->map('array<positive-int, string>', [42 => 'foo']);

$mapper->map('array<negative-int, string>', [-42 => 'foo']);

$mapper->map('array<int<-42, 1337>, string>', [42 => 'foo']);

$mapper->map('array<non-empty-string, string>', ['foo' => 'foo']);

$mapper->map('array<class-string, string>', ['SomeClass' => 'foo']);
```

## Features

* Add support for PHP 8.3 ([5c44f8](https://github.com/CuyZ/Valinor/commit/5c44f83d61def60d13ad7b582d76b2febc63e432))
* Allow any string or integer in array key ([12af3e](https://github.com/CuyZ/Valinor/commit/12af3ed1d396b1dbf81b11a7ad5a0e205b47e74a))
* Support microseconds in the Atom / RFC 3339 / ISO 8601 format ([c25721](https://github.com/CuyZ/Valinor/commit/c25721f838de1e9bf97e5d2c0b301ed2e62429a4))

## Bug Fixes

* Correctly handle type inferring for method coming from interface ([2657f8](https://github.com/CuyZ/Valinor/commit/2657f8bd8836c658753a29737f08b91e145b67b8))
* Detect missing closing bracket after comma in shaped array type ([2aa4b6](https://github.com/CuyZ/Valinor/commit/2aa4b6f0147e31b99b64c59d5d529d4b2230779b))
* Handle class name collision while parsing types inside a class ([044072](https://github.com/CuyZ/Valinor/commit/044072e2cbdd6b12a2b820f3805963a9f8f22860))
* Handle invalid Intl formats with `intl.use_exceptions=1` ([29da9a](https://github.com/CuyZ/Valinor/commit/29da9acd97db6f43bfc7db79b83755cca16734bd))
* Improve cache warmup by creating required directories ([a3341a](https://github.com/CuyZ/Valinor/commit/a3341ab3d7b8b231059037558dbd7f87f420160c))
* Load attributes lazily during runtime and cache access ([3e7c63](https://github.com/CuyZ/Valinor/commit/3e7c632b78bbf031e49b1ba03a8af24856611a82))
* Properly handle class/enum name in shaped array key ([1964d4](https://github.com/CuyZ/Valinor/commit/1964d4158e1a196a33e1c430728ffd27c1a2d728))

## Other

* Improve attributes arguments compilation ([c4acb1](https://github.com/CuyZ/Valinor/commit/c4acb17b4a21744bdc4260715efec56528d5ca83))
* Replace regex-based type parser with character-based one ([ae8303](https://github.com/CuyZ/Valinor/commit/ae830327b2eca87bc171ac4d44c62fde49daa477))
* Simplify symbol parsing algorithm ([f260cf](https://github.com/CuyZ/Valinor/commit/f260cfb0b1b6e84542a250a8f2f10533afdc021e))
* Update Rector dependency ([669ff9](https://github.com/CuyZ/Valinor/commit/669ff992bd5544bb036646248643325d70e87a1d))

[report any encountered issue on the repository]: https://github.com/CuyZ/Valinor/issues/
[@TimWolla]: https://github.com/TimWolla
