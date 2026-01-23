# Changelog 2.3.2 — 23rd of January 2026

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.3.2

## Notable changes

**End of PHP 8.1 support**

PHP 8.1 security support has ended on the 31st of December 2025.

See: https://www.php.net/supported-versions.php

**Removal of `composer-runtime-api` package dependency**

Using the `composer-runtime-api` library leads to unnecessary IO everytime the
library is used; therefore, we prefer to use a basic constant that contains the
package version.

This change slightly increases performance and makes the package completely
dependency free. 🎉

### Bug Fixes

* Properly handle attribute transformers compilation ([747414](https://github.com/CuyZ/Valinor/commit/747414334d4be7ea9b6fc05905bf8ad2d21f96b6))
* Properly handle imported function's namespace resolution ([7757bd](https://github.com/CuyZ/Valinor/commit/7757bdd67fc4cf0eaee5752dd45c17637aa66664))
* Properly handle large string integer casting ([b4d9a4](https://github.com/CuyZ/Valinor/commit/b4d9a477c4306968c49d75f690932cb1b3481234))
* Simplify circular dependency handling ([a7d8e2](https://github.com/CuyZ/Valinor/commit/a7d8e223c5675535c33a06bbdd9758134cfdafce))
* Use native type if advanced type unresolvable in normalizer compile ([121798](https://github.com/CuyZ/Valinor/commit/12179861f50576aadc8d1b50af249274e6821b58))

##### Cache

* Only unlink temp file if still exists ([58b89c](https://github.com/CuyZ/Valinor/commit/58b89c4e022870c03ac4c5ed42abcf5fd6c7a078))

### Internal

* Remove unused exception ([aad781](https://github.com/CuyZ/Valinor/commit/aad7817c6e608952c7f7cccfe9688589dc10700b))
* Replace `composer-runtime-api` requirement by PHP constant usage ([8152be](https://github.com/CuyZ/Valinor/commit/8152be41495074b59be1515a79b51d87aaf911d7))
* Standardize documentation comments ([274207](https://github.com/CuyZ/Valinor/commit/274207352f636817a0904b9aa48d0a8f7dc8ad30))
* Use internal interface for mapping logical exception ([8e00d3](https://github.com/CuyZ/Valinor/commit/8e00d345e7ef2eca02bc4e4fcd87a8f15c53ea4c))

### Other

* Drop support for PHP 8.1 ([fec22a](https://github.com/CuyZ/Valinor/commit/fec22a5929621106146d30386466de5423661dc9))
* Separate unexpected mapped keys in own errors ([332ef6](https://github.com/CuyZ/Valinor/commit/332ef69e675f803f99280cbd1df6cc7b5d0c57e5))
