# Changelog 1.15.0 — 31st of March 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.15.0

## Notable changes

**Normalizer compilation**

A new compilation step for the normalizer has been implemented, which aims to
bring huge performance gains when normalizing any value. It works by adding a
static analysis pass to the process, which will recursively analyse how the
normalizer should perform for every value it can meet. This process results in a
native PHP code entry, that can then be cached for further usage.

This compilation cache feature is automatically enabled when [adding the cache
in the mapper builder]. This *should* be transparent for most users, but as
this is a major change in the code (see [Pull Request #500]), some bugs may have
slipped through. If you encounter such issues that look related to this change,
[please open an issue] and we will try to fix it as soon as possible.

!!! note "Sponsoring and notes about the future"

    My goal remains to provide users of this library with the best possible 
    experience. To that end, motivational messages and financial support are
    greatly appreciated. If you use this library and find it useful, please
    consider [sponsoring the project on GitHub] 🤗
    
    The development of this feature took nearly two years, mainly due to limited
    spare time to work on it. I hope you enjoy this feature as much as I enjoyed
    building it!
    
    On a side note, the next major project will be adding a compiled cache entry
    feature for mappers, similar to how it was implemented for normalizers. Stay
    tuned…

[adding the cache in the mapper builder]: https://valinor.cuyz.io/latest/other/performance-and-caching/
[Pull Request #500]: https://github.com/CuyZ/Valinor/pull/500
[please open an issue]: https://github.com/CuyZ/Valinor/issues/new
[sponsoring the project on GitHub]: https://github.com/CuyZ/Valinor?sponsor=1

### Features

* Introduce compiled normalizer cache ([a4b2a7](https://github.com/CuyZ/Valinor/commit/a4b2a7577ac750fa817bd4f2b7960a1459c31e7f))

### Bug Fixes

* Accept an object implementing an interface without infer setting ([edd488](https://github.com/CuyZ/Valinor/commit/edd488419bc3d2cb1a18dfd337ebc44379f77007))
* Handle self-referential types in object constructors ([dc7b6a](https://github.com/CuyZ/Valinor/commit/dc7b6a8cf233b37121bf2cdb0970aabcf33856d8))
* Properly handle interface with no implementation in union type ([f3f98d](https://github.com/CuyZ/Valinor/commit/f3f98d8d5964fcb370d27ce8c7b27578f5ebb7e7))
* Properly match class-string type with no subtype ([c8fe90](https://github.com/CuyZ/Valinor/commit/c8fe90937490ebc1fce67063b069944e167978b4))

### Other

* Add methods to fetch native types ([7213eb](https://github.com/CuyZ/Valinor/commit/7213eb4376f85ecd601a9ba3de60dd205ed41574))
* Improve integer value type match algorithm ([048a48](https://github.com/CuyZ/Valinor/commit/048a481f8d634d1c2ebab9fe7e224c582f35e80a))
* Update default error message for invalid value for union type ([d1ab6a](https://github.com/CuyZ/Valinor/commit/d1ab6a1b6e4fad2b1d1f404bf310963518ba8d1c))
