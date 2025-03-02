# Changelog 1.14.3 — 17th of February 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.14.3

### Bug Fixes

* Normalize empty iterable object as empty array in JSON ([a22a53](https://github.com/CuyZ/Valinor/commit/a22a5379986ab79538ed2971551bd13f6632346a))
* Properly handle full namespaced enum type in docblock ([eb8816](https://github.com/CuyZ/Valinor/commit/eb8816c0bfa200d50bec0346bc86a9f1fe1a6ec4))
* Support PHPStan extension for PHPStan v1 and v2 ([9f043b](https://github.com/CuyZ/Valinor/commit/9f043b6e45a885a5339c69006b894a3f248955cd))

### Other

* Handle trailing comma in shaped array declaration ([1cb4e9](https://github.com/CuyZ/Valinor/commit/1cb4e9536f966dbfc0def26380ebfdc1e9e014cc))
* Make sure a child shell is not root ([ef0b5c](https://github.com/CuyZ/Valinor/commit/ef0b5c950cea2616028fe0dd2dfab31399c7e439))
* Refactor node builders stack ([040b90](https://github.com/CuyZ/Valinor/commit/040b9093f16a7446f6e678ae992754c8170bcad9))
* Remove `ErrorCatcherNodeBuilder` ([f8eedc](https://github.com/CuyZ/Valinor/commit/f8eedc944978ecfaaa0487ac3a9068be1e7f8747))
* Remove `IterableNodeBuilder` ([339f10](https://github.com/CuyZ/Valinor/commit/339f106614033c6871137552cfd628b02db0914d))
* Remove the need to keep a reference to the Shell parent node ([070db3](https://github.com/CuyZ/Valinor/commit/070db32a27872be672c565aa3203c6da988b593e))
