# Changelog 1.4.0 — 17th of April 2023

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.4.0

## Notable changes

**Exception thrown when source is invalid**

JSON or YAML given to a source may be invalid, in which case an exception can
now be caught and manipulated.

```php
try {
    $source = \CuyZ\Valinor\Mapper\Source\Source::json('invalid JSON');
} catch (\CuyZ\Valinor\Mapper\Source\Exception\InvalidSource $error) {
    // Let the application handle the exception in the desired way.
    // It is possible to get the original source with `$error->source()`
}
```

## Features

* Introduce `InvalidSource` thrown when using invalid JSON/YAML ([0739d1](https://github.com/CuyZ/Valinor/commit/0739d128caa66817ab278bc849ed836fc041b88b))

## Bug Fixes

* Allow integer values in float types ([c6df24](https://github.com/CuyZ/Valinor/commit/c6df24da2bd1bb013ff0aee7c932983646fa4b0e))
* Make `array-key` type match `mixed` ([ccebf7](https://github.com/CuyZ/Valinor/commit/ccebf78c997eb89caa9bb2b73e82b25d9710e6a3))
* Prevent infinite loop when class has parent class with same name ([83eb05](https://github.com/CuyZ/Valinor/commit/83eb058478cc7dc162a77bb2800d2fbd1839639d))

## Other

* Add previous exception in various custom exceptions ([b9e381](https://github.com/CuyZ/Valinor/commit/b9e381604c90ac5531e1e7ae6b63b9abda36126e))
