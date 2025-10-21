# Changelog 2.3.0 — 21st of October 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.3.0

## Notable new features

**PHP 8.5 support 🐘**

Enjoy the upcoming PHP 8.5 version before it is even officially released!

**Performance improvements**

The awesome [Markus Staab] has identified some performance bottlenecks in the
codebase, leading to changes that improved the execution time of the mapper by
~50% in his case (and probably some of yours)!

[Markus Staab]: https://github.com/staabm

**Incoming HTTP request mapping**

There is an ongoing discussion to add support for HTTP request mapping, if
that's something you're interested in, please [join the discussion](
https://github.com/CuyZ/Valinor/discussions/736)!

### Features

* Add support for closures in attributes ([d25d6f](https://github.com/CuyZ/Valinor/commit/d25d6fc9aa940ac145ddaf87540d4b3773ca4576))
* Add support for PHP 8.5 ([7c34e7](https://github.com/CuyZ/Valinor/commit/7c34e7ad84e8a1fb848307434a850d07ce627355))

### Other

* Support empty shaped array ([a3eec8](https://github.com/CuyZ/Valinor/commit/a3eec8c70208a832787ed5b9ba46727ee1288af9))

### Internal

* Change compiled transformer method hashing algo ([cf112b](https://github.com/CuyZ/Valinor/commit/cf112bf595fbe0841318a4b2dd2348224bec8754))
* Micro-optimize arguments conversion to shaped array ([33346d](https://github.com/CuyZ/Valinor/commit/33346da58d365edfbb57ae39e48dd66c45b2931c))
* Use memoization for `ShapedArrayType::toString()` ([4fcfb6](https://github.com/CuyZ/Valinor/commit/4fcfb6a3140bdab9603832a02d53d9fc1a737181))
* Use memoization for arguments' conversion to shaped array ([0f83be](https://github.com/CuyZ/Valinor/commit/0f83be5c57883a4767adaa0e5f12a6ed42388bfb))
* Use memoization for type dumping ([f47613](https://github.com/CuyZ/Valinor/commit/f4761321c2147e04dd556d60bf7fa379b8ddb9be))
