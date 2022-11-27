# Changelog 0.6.0 — 24th of February 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.6.0

## ⚠ BREAKING CHANGES

* Improve interface inferring API ([1eb6e6](https://github.com/CuyZ/Valinor/commit/1eb6e6191368453b2c982068a4ceac42681dcbe8))
* Improve object binding API ([6d4270](https://github.com/CuyZ/Valinor/commit/6d427088f7141a860ce89e5874fdcdf3abb528b6))

## Features

* Handle variadic parameters in constructors ([b6b329](https://github.com/CuyZ/Valinor/commit/b6b329663853aec3597041eea3d936186c410621))
* Improve value altering API ([422e6a](https://github.com/CuyZ/Valinor/commit/422e6a8b272ee1955fef904a43c8bf3721b70ae1))
* Introduce a camel case source key modifier ([d94652](https://github.com/CuyZ/Valinor/commit/d9465222f463896f84379f6e6f1cfef016a0470d))
* Introduce function definition repository ([b49ebf](https://github.com/CuyZ/Valinor/commit/b49ebf37be630d4a26e3525ecaedf89fbaa53520))
* Introduce method to get parameter by index ([380961](https://github.com/CuyZ/Valinor/commit/380961247e065155ce2fdd212f8a51bbe82e931e))

## Bug Fixes

* Change license in `composer.json` ([6fdd62](https://github.com/CuyZ/Valinor/commit/6fdd62dfc2fd68b29b6d915e1f3a81f54e5aea51))
* Ensure native mixed types remain valid ([18ccbe](https://github.com/CuyZ/Valinor/commit/18ccbebb9a4484e4efb4db2c5c9405e853578e7d))
* Remove string keys when unpacking variadic parameter values ([cbf4e1](https://github.com/CuyZ/Valinor/commit/cbf4e11154ae428323099db7e689494624f63293))
* Transform exception thrown during object binding into a message ([359e32](https://github.com/CuyZ/Valinor/commit/359e32d03d062610aab4c171a943b28a09cbaf0f))
* Write temporary cache file inside cache subdirectory ([1b80a1](https://github.com/CuyZ/Valinor/commit/1b80a1df9d64e4c835362b34b1dec9b38ad083d3))

## Other

* Check value acceptance in separate node builder ([30d447](https://github.com/CuyZ/Valinor/commit/30d4479aefdeb09c7cea20cb4daea1f2b724871b))
* Narrow union types during node build ([06e9de](https://github.com/CuyZ/Valinor/commit/06e9dedfd8742be8946b57aedc8d03945eae36fc))
