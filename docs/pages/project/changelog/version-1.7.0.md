# Changelog 1.7.0 — 23rd of October 2023

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.7.0

## Notable changes

**Non-positive integer**

Non-positive integer can be used as below. It will accept any value equal to or
lower than zero.

```php
final class SomeClass
{
    /** @var non-positive-int */
    public int $nonPositiveInteger;
}
```

**Non-negative integer**

Non-negative integer can be used as below. It will accept any value equal to or
greater than zero.

```php
final class SomeClass
{
    /** @var non-negative-int */
    public int $nonNegativeInteger;
}
```

## Features

* Handle non-negative integer type ([f444ea](https://github.com/CuyZ/Valinor/commit/f444eae1f1205a9bbba75670524dbb6799e576aa))
* Handle non-positive integer type ([53e404](https://github.com/CuyZ/Valinor/commit/53e4047f12718b985822926b57226805671206b9))

## Bug Fixes

* Add missing `@psalm-pure` annotation to pure methods ([004eb1](https://github.com/CuyZ/Valinor/commit/004eb16717d2f16dc899e1a6b2c0d987ee0f77c1))
* Handle comments in classes when parsing types imports ([3b663a](https://github.com/CuyZ/Valinor/commit/3b663a903abe6b7ea5bbbe41281d8ea711be7226))

## Other

* Add comment for future PHP version change ([461898](https://github.com/CuyZ/Valinor/commit/46189854c335565317edbf42e36c01fca8c3614b))
* Fix some typos ([5cf8ae](https://github.com/CuyZ/Valinor/commit/5cf8ae54d57dee1b92097d0e01e06105b73b7732))
* Make `NativeBooleanType` a `BooleanType` ([d57ffa](https://github.com/CuyZ/Valinor/commit/d57ffae91b961019ec5fcb8344fddb8921f750dd))
