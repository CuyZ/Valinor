# Changelog 1.5.0 — 7th of August 2023

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.5.0

## Features

* Introduce method to get date formats supported during mapping ([873961](https://github.com/CuyZ/Valinor/commit/873961e8c17474bb4d96be6ea84390969d686d96))

## Bug Fixes

* Allow filesystem cache to be cleared when directory does not exist ([782408](https://github.com/CuyZ/Valinor/commit/78240837e70a4a5f987e300de20478287e17224b))
* Allow negative timestamp to be mapped to a datetime ([d358e8](https://github.com/CuyZ/Valinor/commit/d358e83bf94dd37359ce4d52388cf7c8639d98ec))
* Allow overriding of supported datetime formats ([1c70c2](https://github.com/CuyZ/Valinor/commit/1c70c2d29ad9a70acc952031e5726e4ea492bad3))
* Correctly handle message formatting for long truncated UTF8 strings ([0a8f37](https://github.com/CuyZ/Valinor/commit/0a8f37b15572107f321c9525573bc5de82463048))
* Make serialization of attributes possible ([e8ca2f](https://github.com/CuyZ/Valinor/commit/e8ca2ff93c241034789d9af938bd7f5bbffd3d37))
* Remove exception inheritance from `UnresolvableType` ([eaa128](https://github.com/CuyZ/Valinor/commit/eaa1283ae15e00b64552abcd28c0236033577992))
* Remove previous exception from `UnresolvableType` ([5c89c6](https://github.com/CuyZ/Valinor/commit/5c89c66f0bdd87b4b50c401d1de22b72f4fde513))

## Other

* Avoid using `unserialize` when caching `NULL` default values ([5e9b4c](https://github.com/CuyZ/Valinor/commit/5e9b4ce217cf43cd400127a76be73fd4fadd54c1))
* Catch `json_encode` exception to help identifying parsing errors ([861c3b](https://github.com/CuyZ/Valinor/commit/861c3b9da7f5c762db11da2ba9bbfc1cbdeaace8))
* Update dependencies ([c31e5c](https://github.com/CuyZ/Valinor/commit/c31e5c9eef02076d665b2d26c10d868a0414934e), [5fa107](https://github.com/CuyZ/Valinor/commit/5fa1076e11d86e9f057ed58bd1d8c4a7d38580c3))
