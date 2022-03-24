<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [0.7.0](https://github.com/romm/Valinor/compare/0.6.0...0.7.0) (2022-03-24)
### ⚠ BREAKING CHANGES

* Change `Attributes::ofType` return type to `array` ([1a599b](https://github.com/romm/Valinor/commit/1a599b0bdf5cf07385ed120817f1a4720064e4dc))
* Introduce method to register constructors used during mapping ([ecafba](https://github.com/romm/Valinor/commit/ecafba3b21cd48f38a5e5d2b5b7f97012342536a))

### Features

* Introduce a path-mapping source modifier ([b7a7d2](https://github.com/romm/Valinor/commit/b7a7d22993eb2758f5beb80e58e599df60258bf3))
* Introduce a source builder ([ad5103](https://github.com/romm/Valinor/commit/ad51039cc3f76245eb96fab21a0f2709fcf13bdb))

### Bug Fixes

* Handle numeric key with camel case source key modifier ([b8a18f](https://github.com/romm/Valinor/commit/b8a18feadcbb564d80a34ca004c584c56ac0de04))
* Handle parameter default object value compilation ([fdef93](https://github.com/romm/Valinor/commit/fdef93074c32cf36072446da8a3466a1bf781dbb))
* Handle variadic arguments in callable constructors ([b646cc](https://github.com/romm/Valinor/commit/b646ccecf25ca17ecf8fec4e03bb91748db9527d))
* Properly handle alias types for function reflection ([e5b515](https://github.com/romm/Valinor/commit/e5b5157eaf0b60e10f1060bf22cc4e2bd7d828c7))

### Other

* Add Striker HTML report when running infection ([79c7a4](https://github.com/romm/Valinor/commit/79c7a4563d545d5513f96ec1b5f00abbd50b5881))
* Handle class name in function definition ([e2451d](https://github.com/romm/Valinor/commit/e2451df2c1857a935a1fc2e88c056d6b414ed924))
* Introduce functions container to wrap definition handling ([fd1117](https://github.com/romm/Valinor/commit/fd11177b06c9740c621155c2f635e012c2f8651e))


---

## [0.6.0](https://github.com/romm/Valinor/compare/0.5.0...0.6.0) (2022-02-24)
### ⚠ BREAKING CHANGES

* Improve interface inferring API ([1eb6e6](https://github.com/romm/Valinor/commit/1eb6e6191368453b2c982068a4ceac42681dcbe8))
* Improve object binding API ([6d4270](https://github.com/romm/Valinor/commit/6d427088f7141a860ce89e5874fdcdf3abb528b6))

### Features

* Handle variadic parameters in constructors ([b6b329](https://github.com/romm/Valinor/commit/b6b329663853aec3597041eea3d936186c410621))
* Improve value altering API ([422e6a](https://github.com/romm/Valinor/commit/422e6a8b272ee1955fef904a43c8bf3721b70ae1))
* Introduce a camel case source key modifier ([d94652](https://github.com/romm/Valinor/commit/d9465222f463896f84379f6e6f1cfef016a0470d))
* Introduce function definition repository ([b49ebf](https://github.com/romm/Valinor/commit/b49ebf37be630d4a26e3525ecaedf89fbaa53520))
* Introduce method to get parameter by index ([380961](https://github.com/romm/Valinor/commit/380961247e065155ce2fdd212f8a51bbe82e931e))

### Bug Fixes

* Change license in `composer.json` ([6fdd62](https://github.com/romm/Valinor/commit/6fdd62dfc2fd68b29b6d915e1f3a81f54e5aea51))
* Ensure native mixed types remain valid ([18ccbe](https://github.com/romm/Valinor/commit/18ccbebb9a4484e4efb4db2c5c9405e853578e7d))
* Remove string keys when unpacking variadic parameter values ([cbf4e1](https://github.com/romm/Valinor/commit/cbf4e11154ae428323099db7e689494624f63293))
* Transform exception thrown during object binding into a message ([359e32](https://github.com/romm/Valinor/commit/359e32d03d062610aab4c171a943b28a09cbaf0f))
* Write temporary cache file inside cache subdirectory ([1b80a1](https://github.com/romm/Valinor/commit/1b80a1df9d64e4c835362b34b1dec9b38ad083d3))

### Other

* Check value acceptance in separate node builder ([30d447](https://github.com/romm/Valinor/commit/30d4479aefdeb09c7cea20cb4daea1f2b724871b))
* Narrow union types during node build ([06e9de](https://github.com/romm/Valinor/commit/06e9dedfd8742be8946b57aedc8d03945eae36fc))


---

## [0.5.0](https://github.com/CuyZ/Valinor/compare/0.4.0...0.5.0) (2022-01-27)
### Features

* Introduce automatic named constructor resolution ([718d3c](https://github.com/CuyZ/Valinor/commit/718d3c1bc2ea7d28b4b1f6c062addcd1dde8660b))
* Set up dependabot for automated weekly dependency upgrades ([23b611](https://github.com/CuyZ/Valinor/commit/23b6113869abbcf8d13f4702b69fca59f61b9205))
* Simplify type signature of `TreeMapper#map()` ([e28003](https://github.com/CuyZ/Valinor/commit/e2800339411b68a24eebc266377184c76ef2701e))

### Bug Fixes

* Correct regex that detects [*@internal*](https://github.com/internal) or [*@api*](https://github.com/api) annotations ([39f0b7](https://github.com/CuyZ/Valinor/commit/39f0b71b94fa762ae7eb4cf9b1d7c871a70a31ef))
* Improve type definitions to allow Psalm automatic inferring ([f9b04c](https://github.com/CuyZ/Valinor/commit/f9b04c5c2717eaca1b7bb86fc6311917f05c82b0))
* Return indexed list of attributes when filtering on type ([66aa4d](https://github.com/CuyZ/Valinor/commit/66aa4d688ac162093b9fde3579d08795e1e61cc9))


---

## [0.4.0](https://github.com/romm/Valinor/compare/0.3.0...0.4.0) (2022-01-07)
### ⚠ BREAKING CHANGES

* Add access to root node when error occurs during mapping ([54f608](https://github.com/romm/Valinor/commit/54f608e5b1a4bbd508246c063a3e79df48c1ddeb))
* Allow mapping to any type ([b2e810](https://github.com/romm/Valinor/commit/b2e810e3ce997181b7cfcc96d2bccb9c56a5bdd8))
* Allow object builder to yield arguments without source ([8a7414](https://github.com/romm/Valinor/commit/8a74147d4c1f78696049c2dfb9acf979ef6e4689))
* Wrap node messages in proper class ([a805ba](https://github.com/romm/Valinor/commit/a805ba0442f9ac8dd6b2499426ffa6fcb190d4be))

### Features

* Introduce automatic union of objects inferring during mapping ([79d7c2](https://github.com/romm/Valinor/commit/79d7c266ecabc069b11120338ae1e62a8f3dca97))
* Introduce helper class `MessageMapFormatter` ([ddf69e](https://github.com/romm/Valinor/commit/ddf69efaaa4eeab87126bdc7f43b92d5faa06f46))
* Introduce helper class `MessagesFlattener` ([a97b40](https://github.com/romm/Valinor/commit/a97b406154fa5bccc2f874634ed24af69401cd4a))
* Introduce helper `NodeTraverser` for recursive operations on nodes ([cc1bc6](https://github.com/romm/Valinor/commit/cc1bc66bbece7f79dfec093e33e96d3e31bca4e9))

### Bug Fixes

* Handle nested attributes compilation ([d2795b](https://github.com/romm/Valinor/commit/d2795bc6b9ee53f9896889cabc1006be2586b008))
* Treat forbidden mixed type as invalid type ([36bd36](https://github.com/romm/Valinor/commit/36bd3638c8e48d2c43d247991b8d2055e8ab56bb))
* Treat union type resolving error as message ([e834cd](https://github.com/romm/Valinor/commit/e834cdc5d3384d4295582f8d602b5da5f656e83a))
* Use locked package versions for quality assurance workflow ([626f13](https://github.com/romm/Valinor/commit/626f135eee5b4954845e954d567babd58f6ca51b))

### Other

* Ignore changelog configuration file in git export ([85a6a4](https://github.com/romm/Valinor/commit/85a6a49ce20b96f2e023a7e824b8085ea198fd39))
* Raise PHPStan version ([0144bf](https://github.com/romm/Valinor/commit/0144bf084a4883ecbdb65155a05b2ea90e7fca61))


---

## [0.3.0](https://github.com/romm/Valinor/compare/0.2.0...0.3.0) (2021-12-18)
### Features

* Handle common database datetime formats (#40) ([179ba3](https://github.com/romm/Valinor/commit/179ba3df299be62b0eac24a80e9d4d56d0bb5074))

### Other

* Change Composer scripts calls ([0b507c](https://github.com/romm/Valinor/commit/0b507c9b330b76e29319dd5933aa5760df3e3c8d))
* Raise version of `friendsofphp/php-cs-fixer` ([e5ccbe](https://github.com/romm/Valinor/commit/e5ccbe201b8767066e0e0510e606f9492f3270f1))


---

## [0.2.0](https://github.com/romm/Valinor/compare/0.1.1...0.2.0) (2021-12-07)
### Features

* Handle integer range type ([9f99a2](https://github.com/romm/Valinor/commit/9f99a2a1eff3a1a120a9fa80ecb7045eeafdbfd3))
* Handle local type aliasing in class definition ([56142d](https://github.com/romm/Valinor/commit/56142dea5b7c2eb7fdd591ede2f2bd0a1dd3e7b4))
* Handle type alias import in class definition ([fa3ce5](https://github.com/romm/Valinor/commit/fa3ce50dfb069a3e908d5abdb59bd70b3b7d3a90))

### Bug Fixes

* Do not accept shaped array with excessive key(s) ([5a578e](https://github.com/romm/Valinor/commit/5a578ea4c26e82fe2bea0bbdba821ff2cd2de03d))
* Handle integer value match properly ([9ee2cc](https://github.com/romm/Valinor/commit/9ee2cc471ebded7b0b4416d1ed245752090f32be))

### Other

* Delete commented code ([4f5612](https://github.com/romm/Valinor/commit/4f561290b1ba71dc15f1b2806d2ea929429d5910))
* Move exceptions to more specific folder ([185edf](https://github.com/romm/Valinor/commit/185edf60534391475da4d90a4fec4bab58c9e1c8))
* Rename `GenericAssignerLexer` to `TypeAliasLexer` ([680941](https://github.com/romm/Valinor/commit/680941687b5e0488eebbfc4372162c25fab34bcb))
* Use `marcocesarato/php-conventional-changelog` for changelog ([178aa9](https://github.com/romm/Valinor/commit/178aa97687bd1b2de1c21a57b20476629d2de748))


---

## [0.1.1](https://github.com/romm/Valinor/compare/0.1.0...0.1.1) (2021-12-01)
### ⚠ BREAKING CHANGES

* Change license from GPL 3 to MIT ([a77b28](https://github.com/romm/Valinor/commit/a77b28c5c224f6cb1232ac17de0002bff8e09ad1))

### Features

* Handle multiline type declaration ([d99c59](https://github.com/romm/Valinor/commit/d99c59dfb568950be3abda683051ac9b358af78e))

### Bug Fixes

* Filter type symbols with strict string comparison ([6cdea3](https://github.com/romm/Valinor/commit/6cdea31bc29e2bdbb2574ee0678e71cb42c4761b))
* Handle correctly iterable source during mapping ([dd4624](https://github.com/romm/Valinor/commit/dd4624c5e0376cf1c590117dcad10c659a614701))
* Handle shaped array integer key ([5561d0](https://github.com/romm/Valinor/commit/5561d018abb5b237c7cb8385c9a3032c86711738))
* Resolve single/double quotes when parsing doc-block type ([1c628b](https://github.com/romm/Valinor/commit/1c628b66755497a704ae3333abc8782bac8ee02a))

### Other

* Change PHPStan stub file extension ([8fc6af](https://github.com/romm/Valinor/commit/8fc6af283cebe1b317b9107d4741a5f2bddea095))
* Delete unwanted code ([e3e169](https://github.com/romm/Valinor/commit/e3e169fb3c05a46a23cdf7c0c989b5c637a0bd6d))
* Syntax highlight stub files (#9) ([9ea95f](https://github.com/romm/Valinor/commit/9ea95f43f3b8b750c6c147d25cc77a602a696172))
* Use composer runtime API ([1f754a](https://github.com/romm/Valinor/commit/1f754a7e77c9cf3faffccddfdc5179ea1f12840b))


---
