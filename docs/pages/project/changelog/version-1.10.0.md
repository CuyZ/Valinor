# Changelog 1.10.0 — 12th of March 2024

!!! info inline end "[See release on GitHub]"
[See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.9.0

## Notable changes

**Dropping support for PHP 8.0**

PHP 8.0 security support has ended [on the 26th of November
2023](https://www.php.net/supported-versions.php). Therefore, we are dropping
support for PHP 8.0 in this version.

If any security issue was to be found, we might consider backporting the fix to
the 1.9.x version if people need it, but we strongly recommend upgrading your
application to a supported PHP version.

**Introducing `Constructor` attribute**

A long awaited feature has landed in the library!

The `Constructor` attribute can be assigned to any method inside an object, to
automatically mark the method as a constructor for the class. This is a more
convenient way of registering constructors than using the
`MapperBuilder::registerConstructor` method, although it does not replace it.

The method targeted by a `Constructor` attribute must be public, static and
return an instance of the class it is part of.

```php
final readonly class Email
{
    // When another constructor is registered for the class, the native
    // constructor is disabled. To enable it again, it is mandatory to
    // explicitly register it again.
    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public function __construct(public string $value) {}

    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public static function createFrom(
        string $userName, string $domainName
    ): self {
        return new self($userName . '@' . $domainName);
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(Email::class, [
        'userName' => 'john.doe',
        'domainName' => 'example.com',
    ]); // john.doe@example.com
```

### Features

* Introduce `Constructor` attribute ([d86295](https://github.com/CuyZ/Valinor/commit/d86295c2fe2e7ea7ed37d00fd39f20f31a694129))

### Bug Fixes

* Properly encode scalar value in JSON normalization ([2107ea](https://github.com/CuyZ/Valinor/commit/2107ea1847aaa925a47ce3465974b72810b24bea))
* Properly handle list type when input contains superfluous keys ([1b8efa](https://github.com/CuyZ/Valinor/commit/1b8efa1a90e46107e25079ce520f08642ccd65c6))

### Other

* Drop support for PHP 8.0 ([dafcc8](https://github.com/CuyZ/Valinor/commit/dafcc80c6d135535c1dbeba9bcee641f5d0c0801))
* Improve internal definitions string types ([105281](https://github.com/CuyZ/Valinor/commit/105281b203af9c7290d0a2bd98a709748d00dc9a))
* Refactor file system cache to improve performance ([e692f0](https://github.com/CuyZ/Valinor/commit/e692f0d20ac8d252c57e236e4926bc8d9a72679b))
* Remove unneeded closure conversion ([972e65](https://github.com/CuyZ/Valinor/commit/972e6572b899db72d09dfa68dc4eae87a05a51f1))
* Update dependencies ([c5627f](https://github.com/CuyZ/Valinor/commit/c5627ffe735395ebc8e031e7313a483119d5128d))
