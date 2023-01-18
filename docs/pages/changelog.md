---
toc_depth: 2
---

<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [0.17.1](https://github.com/CuyZ/Valinor/compare/0.17.0...0.17.1) (2023-01-18)

### Bug Fixes

* Use PHP 8.0 Polyfill where needed ([d90a95](https://github.com/CuyZ/Valinor/commit/d90a950de403e61da417043eeb4025eaab657ef7))

## [0.17.0](https://github.com/CuyZ/Valinor/compare/0.16.0...0.17.0) (2022-11-08)

### Notable changes

The main feature introduced in this release is the split of the flexible mode in
three distinct modes:

1. **The flexible casting**

   Changes the behaviours explained below:

   ```php
   $flexibleMapper = (new \CuyZ\Valinor\MapperBuilder())
       ->enableFlexibleCasting()
       ->mapper();

   // ---
   // Scalar types will accept non-strict values; for instance an
   // integer type will accept any valid numeric value like the
   // *string* "42".

   $flexibleMapper->map('int', '42');
   // => 42

   // ---
   // List type will accept non-incremental keys.

   $flexibleMapper->map('list<int>', ['foo' => 42, 'bar' => 1337]);
   // => [0 => 42, 1 => 1338]

   // ---
   // If a value is missing in a source for a node that accepts `null`,
   // the node will be filled with `null`.

   $flexibleMapper->map(
       'array{foo: string, bar: null|string}',
       ['foo' => 'foo'] // `bar` is missing
   );
   // => ['foo' => 'foo', 'bar' => null]

   // ---
   // Array and list types will convert `null` or missing values to an
   // empty array.

   $flexibleMapper->map(
       'array{foo: string, bar: array<string>}',
       ['foo' => 'foo'] // `bar` is missing
   );
   // => ['foo' => 'foo', 'bar' => []]
   ```

2. **The superfluous keys**

   Superfluous keys in source arrays will be allowed, preventing errors
   when a value is not bound to any object property/parameter or shaped
   array element.

   ```php
   (new \CuyZ\Valinor\MapperBuilder())
       ->allowSuperfluousKeys()
       ->mapper()
       ->map(
           'array{foo: string, bar: int}',
           [
               'foo' => 'foo',
               'bar' => 42,
               'baz' => 1337.404, // `baz` will be ignored
           ]
       );
   ```

3. **The permissive types**

   Allows permissive types `mixed` and `object` to be used during
   mapping.

   ```php
   (new \CuyZ\Valinor\MapperBuilder())
       ->allowPermissiveTypes()
       ->mapper()
       ->map(
           'array{foo: string, bar: mixed}',
           [
               'foo' => 'foo',
               'bar' => 42, // Could be any value
           ]
       );
   ```

### Features

* Add support for `strict-array` type ([d456eb](https://github.com/CuyZ/Valinor/commit/d456ebe003b1335701c577c256be5df1ad4445e7))
* Introduce new callback message formatter ([93f898](https://github.com/CuyZ/Valinor/commit/93f898c9a68cea8de3615b20271e93abde83aff3))
* Introduce new helper class to list messages ([513827](https://github.com/CuyZ/Valinor/commit/513827d5b39b7d122be958f56cf83eb7e743edaa))
* Split mapper flexible mode in three distinct modes ([549e5f](https://github.com/CuyZ/Valinor/commit/549e5fe183be2191501af8f98df00a84f97ee990))

### Bug Fixes

* Allow missing and null value for array node in flexible mode ([034f1c](https://github.com/CuyZ/Valinor/commit/034f1c51e1d2416624eba99a26da057af3159dda))
* Allow missing value for shaped array nullable node in flexible mode ([08fb0e](https://github.com/CuyZ/Valinor/commit/08fb0e17ba952d42fafade5bea0e83461b02ae6e))
* Handle scalar value casting in union types only in flexible mode ([752ad9](https://github.com/CuyZ/Valinor/commit/752ad9d12eabbb5cd3a8300620838243e7e3335b))

### Other

* Do not use `uniqid()` ([b81847](https://github.com/CuyZ/Valinor/commit/b81847839df64006576f54d3bc3cfeadc425f74b))
* Transform missing source value to `null` in flexible mode ([92a41a](https://github.com/CuyZ/Valinor/commit/92a41a1564f32eb39df7f6c20cbead088feebe2b))

---

## [0.16.0](https://github.com/CuyZ/Valinor/compare/0.15.0...0.16.0) (2022-10-19)

### Features

* Add support for PHP 8.2 ([a92360](https://github.com/CuyZ/Valinor/commit/a92360897e8d73fe9509544e61f65afd6b09e5cd))

### Bug Fixes

* Properly handle quote char in type definition ([c71d6a](https://github.com/CuyZ/Valinor/commit/c71d6aef94b5816e3ff7e14d27a82a65f80f7267))

### Other

* Update dependencies ([c2de32](https://github.com/CuyZ/Valinor/commit/c2de3244938da1f44459762b6f17bd480d6346e6))

---

## [0.15.0](https://github.com/CuyZ/Valinor/compare/0.14.0...0.15.0) (2022-10-06)

### Notable changes

Two similar features are introduced in this release: constants and enums 
wildcard notations. This is mainly useful when several cases of an enum or class
constants share a common prefix.

Example for class constants:

```php
final class SomeClassWithConstants
{
    public const FOO = 1337;

    public const BAR = 'bar';

    public const BAZ = 'baz';
}

$mapper = (new MapperBuilder())->mapper();

$mapper->map('SomeClassWithConstants::BA*', 1337); // error
$mapper->map('SomeClassWithConstants::BA*', 'bar'); // ok
$mapper->map('SomeClassWithConstants::BA*', 'baz'); // ok
```

Example for enum:

```php
enum SomeEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}

$mapper = (new MapperBuilder())->mapper();

$mapper->map('SomeEnum::BA*', 'foo'); // error
$mapper->map('SomeEnum::BA*', 'bar'); // ok
$mapper->map('SomeEnum::BA*', 'baz'); // ok
```

### Features

* Add support for class constant type ([1244c2](https://github.com/CuyZ/Valinor/commit/1244c2d68f3478560241e89f74f28b36d6fc2888))
* Add support for wildcard in enumeration type ([69ebd1](https://github.com/CuyZ/Valinor/commit/69ebd19ee84cc56cc8c986f1b5aff299e8d62b5c))
* Introduce utility class to build messages ([cb8792](https://github.com/CuyZ/Valinor/commit/cb87925aac45ca1babea3f34b6cd6e24c9172905))

### Bug Fixes

* Add return types for cache implementations ([0e8f12](https://github.com/CuyZ/Valinor/commit/0e8f12e5f7ffd2193c8ab772dd98d5e1cc858b59))
* Correctly handle type inferring during mapping ([37f96f](https://github.com/CuyZ/Valinor/commit/37f96f101d95e843f6a83d7e29c84b945938a691))
* Fetch correct node value for children ([3ee526](https://github.com/CuyZ/Valinor/commit/3ee526cb27816910f5bf27380021fa1399206335))
* Improve scalar values casting ([212b77](https://github.com/CuyZ/Valinor/commit/212b77fd13c7ead3ba7c0aca165ea3af235b1fa9))
* Properly handle static anonymous functions ([c009ab](https://github.com/CuyZ/Valinor/commit/c009ab98cc327c80e6589c22ee0b3c06b56849de))

### Other

* Import namespace token parser inside library ([0b8ca9](https://github.com/CuyZ/Valinor/commit/0b8ca98a2c9051213f3a59be513f16199288d45f))
* Remove unused code ([b2889a](https://github.com/CuyZ/Valinor/commit/b2889a3ba022410a1929c969acae8e581f670535), [de8aa9](https://github.com/CuyZ/Valinor/commit/de8aa9f4402c7e83a3575a6143eb26b45ea13461))
* Save type token symbols during lexing ([ad0f8f](https://github.com/CuyZ/Valinor/commit/ad0f8fee17773ec226bc4b1eb12370bfcd437187))

---

## [0.14.0](https://github.com/CuyZ/Valinor/compare/0.13.0...0.14.0) (2022-09-01)

### Notable changes

Until this release, the behaviour of the date objects creation was very 
opinionated: a huge list of date formats were tested out, and if one was working
it was used to create the date.

This approach resulted in two problems. First, it led to (minor) performance 
issues, because a lot of date formats were potentially tested for nothing. More
importantly, it was not possible to define which format(s) were to be allowed
(and in result deny other formats).

A new method can now be used in the `MapperBuilder`:

```php
(new \CuyZ\Valinor\MapperBuilder())
    // Both `Cookie` and `ATOM` formats will be accepted
    ->supportDateFormats(DATE_COOKIE, DATE_ATOM)
    ->mapper()
    ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
```

Please note that the old behaviour has been removed. From now on, only valid 
timestamp or ATOM-formatted value will be accepted by default.

If needed and to help with the migration, the following **deprecated** 
constructor can be registered to reactivate the previous behaviour:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        new \CuyZ\Valinor\Mapper\Object\BackwardCompatibilityDateTimeConstructor()
    )
    ->mapper()
    ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
```

### ⚠ BREAKING CHANGES

* Introduce constructor for custom date formats ([f232cc](https://github.com/CuyZ/Valinor/commit/f232cc06363c447692e3df88129d0d31b506bccc))

### Features

* Handle abstract constructor registration ([c37ac1](https://github.com/CuyZ/Valinor/commit/c37ac1e25992d8b194b92d70e6a5727f71c665de))
* Introduce attribute `DynamicConstructor` ([e437d9](https://github.com/CuyZ/Valinor/commit/e437d9405cdff95b1cd9e644464fe38bf98ceb53))
* Introduce helper method to describe supported date formats ([11a7ea](https://github.com/CuyZ/Valinor/commit/11a7ea7252a788f8d9c075872dd82df78788aceb))

### Bug Fixes

* Allow trailing comma in shaped array ([bf445b](https://github.com/CuyZ/Valinor/commit/bf445b5364e11a91786b547ce14f3fe12043cf32))
* Correctly fetch file system cache entries ([48208c](https://github.com/CuyZ/Valinor/commit/48208c1ed1ffcc2ec93d9840b721ed6ce5a7670f))
* Detect invalid constructor handle type ([b3cb59](https://github.com/CuyZ/Valinor/commit/b3cb5927e9578b7519ef773abd0693d186ecaa68))
* Handle classes in a case-sensitive way in type parser ([254074](https://github.com/CuyZ/Valinor/commit/2540741171edce32ace1d59c9410a3bdd1e8e041))
* Handle concurrent cache file creation ([fd39ae](https://github.com/CuyZ/Valinor/commit/fd39aea2a79f216f4e39de52aced905ed75ead0d))
* Handle inherited private constructor in class definition ([73b622](https://github.com/CuyZ/Valinor/commit/73b62241b63ed365a90fb5e44876fafa63680177))
* Handle invalid nodes recursively ([a401c2](https://github.com/CuyZ/Valinor/commit/a401c2a2d696b62d73f0bcccc7fe5b28180ccf99))
* Prevent illegal characters in PSR-16 cache keys ([3c4d29](https://github.com/CuyZ/Valinor/commit/3c4d29901af1419ca39bf05e7d7008c8fe0b5ae6))
* Properly handle callable objects of the same class ([ae7ddc](https://github.com/CuyZ/Valinor/commit/ae7ddcf3caa77f2c4b4ad99dcdbb5f609de55335))

### Other

* Add singleton usage of `ClassStringType` ([4bc50e](https://github.com/CuyZ/Valinor/commit/4bc50e3e42d42798b266026827909b28b1ba8258))
* Change `ObjectBuilderFactory::for` return signature ([57849c](https://github.com/CuyZ/Valinor/commit/57849c92e736335ed2f6b9a50344a1a21dd3bc01))
* Extract native constructor object builder ([2b46a6](https://github.com/CuyZ/Valinor/commit/2b46a60f37966769e3694e743d92382c6e2f8842))
* Fetch attributes for function definition ([ec494c](https://github.com/CuyZ/Valinor/commit/ec494cec489b18a2344796c105af55a33dc39ba0))
* Refactor arguments instantiation ([6414e9](https://github.com/CuyZ/Valinor/commit/6414e9cf14f5c4bb4337dc2c8f2bfa09b7048fd0))


---

## [0.13.0](https://github.com/CuyZ/Valinor/compare/0.12.0...0.13.0) (2022-07-31)

### Notable changes

**Reworking of messages body and parameters features**

The `\CuyZ\Valinor\Mapper\Tree\Message\Message` interface is no longer a
`Stringable`, however it defines a new method `body` that must return the body
of the message, which can contain placeholders that will be replaced by
parameters.

These parameters can now be defined by implementing the interface
`\CuyZ\Valinor\Mapper\Tree\Message\HasParameters`.

This leads to the deprecation of the no longer needed interface
`\CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage` which had a confusing
name.

```php
final class SomeException
    extends DomainException 
    implements ErrorMessage, HasParameters, HasCode
{
    private string $someParameter;

    public function __construct(string $someParameter)
    {
        parent::__construct();

        $this->someParameter = $someParameter;
    }

    public function body() : string
    {
        return 'Some message / {some_parameter} / {source_value}';
    }

    public function parameters(): array
    {
        return [
            'some_parameter' => $this->someParameter,
        ];
    }

    public function code() : string
    {
        // A unique code that can help to identify the error
        return 'some_unique_code';
    }
}
```

**Handle `numeric-string` type**

The new `numeric-string` type can be used in docblocks.

It will accept any string value that is also numeric.

```php
(new MapperBuilder())->mapper()->map('numeric-string', '42'); // ✅
(new MapperBuilder())->mapper()->map('numeric-string', 'foo'); // ❌
```

**Better mapping error message**

The message of the exception will now contain more information, especially the
total number of errors and the source that was given to the mapper. This change
aims to have a better understanding of what is wrong when debugging.

Before:

``Could not map type `array{foo: string, bar: int}` with the given source.``

After:

``Could not map type `array{foo: string, bar: int}`. An error occurred at path 
bar: Value 'some other string' does not match type `int`.``

### ⚠ BREAKING CHANGES

* Rework messages body and parameters features ([ad1207](https://github.com/CuyZ/Valinor/commit/ad1207153ed5158080f14d7400ed2075b4ff478b))

### Features

* Allow to declare parameter for message ([f61eb5](https://github.com/CuyZ/Valinor/commit/f61eb553fa8f5f740b0bc32288d3704f42113fbf))
* Display more information in mapping error message ([9c1e7c](https://github.com/CuyZ/Valinor/commit/9c1e7c928b5ae518d34e50576e90568110662fc6))
* Handle numeric string type ([96a493](https://github.com/CuyZ/Valinor/commit/96a493469ce69b16683cdb8a44cbbd3faa0ab957))
* Make `MessagesFlattener` countable ([2c1c7c](https://github.com/CuyZ/Valinor/commit/2c1c7cf38a905a92b97f3770ddc08ef0a8bdb695))

### Bug Fixes

* Handle native attribute on promoted parameter ([897ca9](https://github.com/CuyZ/Valinor/commit/897ca9b65e12d0e38c5d6d6776a6bf62bccf2fea))

### Other

* Add fixed value for root node path ([0b37b4](https://github.com/CuyZ/Valinor/commit/0b37b48c60a21ef3e01022d566b8812e13547a72))
* Remove types stringable behavior ([b47a1b](https://github.com/CuyZ/Valinor/commit/b47a1bbb5dd523333007a67c6b47256d0b1bff56))

---

## [0.12.0](https://github.com/CuyZ/Valinor/compare/0.11.0...0.12.0) (2022-07-10)

### Notable changes

**SECURITY — Userland exception filtering**

See [advisory GHSA-5pgm-3j3g-2rc7] for more information.

[advisory GHSA-5pgm-3j3g-2rc7]: https://github.com/CuyZ/Valinor/security/advisories/GHSA-5pgm-3j3g-2rc7

Userland exception thrown in a constructor will not be automatically caught by
the mapper anymore. This prevents messages with sensible information from 
reaching the final user — for instance an SQL exception showing a part of a 
query.

To allow exceptions to be considered as safe, the new method
`MapperBuilder::filterExceptions()` must be used, with caution.

```php
final class SomeClass
{
    public function __construct(private string $value)
    {
        \Webmozart\Assert\Assert::startsWith($value, 'foo_');
    }
}

try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->filterExceptions(function (Throwable $exception) {
            if ($exception instanceof \Webmozart\Assert\InvalidArgumentException) {
                return \CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage::from($exception);
            }

            // If the exception should not be caught by this library, it
            // must be thrown again.
            throw $exception;
        })
        ->mapper()
        ->map(SomeClass::class, 'bar_baz');
} catch (\CuyZ\Valinor\Mapper\MappingError $exception) {
    // Should print something similar to:
    // > Expected a value to start with "foo_". Got: "bar_baz"
    echo $exception->node()->messages()[0];
}
```

**Tree node API rework**

The class `\CuyZ\Valinor\Mapper\Tree\Node` has been refactored to remove access
to unwanted methods that were not supposed to be part of the public API. Below 
are a list of all changes:

- New methods `$node->sourceFilled()` and `$node->sourceValue()` allow accessing
  the source value.

- The method `$node->value()` has been renamed to `$node->mappedValue()` and 
  will throw an exception if the node is not valid.

- The method `$node->type()` now returns a string.

- The methods `$message->name()`, `$message->path()`, `$message->type()` and 
  `$message->value()` have been deprecated in favor of the new method 
  `$message->node()`.

- The message parameter `{original_value}` has been deprecated in favor of
  `{source_value}`.

**Access removal of several parts of the library public API**

The access to class/function definition, types and exceptions did not add value 
to the actual goal of the library. Keeping these features under the public API 
flag causes more maintenance burden whereas revoking their access allows more 
flexibility with the overall development of the library.

### ⚠ BREAKING CHANGES

* Filter userland exceptions to hide potential sensible data ([6ce1a4](https://github.com/CuyZ/Valinor/commit/6ce1a439adb1f6ee7e771fe02d454aa91e7b320f))
* Refactor tree node API ([d3b1dc](https://github.com/CuyZ/Valinor/commit/d3b1dcb64ec561cdedffe5ca779341fc9452a858))
* Remove API access from several parts of library ([316d91](https://github.com/CuyZ/Valinor/commit/316d91910d289780a7b791f17b958eae264a6296))
* Remove node visitor feature ([63c87a](https://github.com/CuyZ/Valinor/commit/63c87a2cc4c28546f28d51998a93fe89f0885535))

### Bug Fixes

* Handle inferring methods with same names properly ([dc45dd](https://github.com/CuyZ/Valinor/commit/dc45dd8ac5ab1126a362350dbc5292a421254d54))
* Process invalid type default value as unresolvable type ([7c9ac1](https://github.com/CuyZ/Valinor/commit/7c9ac1dd6d518e5e5f0fc02ee172b12084082d1d))
* Properly display unresolvable type ([3020db](https://github.com/CuyZ/Valinor/commit/3020db20bfa8322e3cb198487851bb5d43ea9894))

### Other

* Ignore `.idea` folder ([84ead0](https://github.com/CuyZ/Valinor/commit/84ead04f84118d18ad0c557db909b0cd10b65252))

---

## [0.11.0](https://github.com/CuyZ/Valinor/compare/0.10.0...0.11.0) (2022-06-23)

### Notable changes

**Strict mode**

The mapper is now more type-sensitive and will fail in the following situations:

- When a value does not match exactly the awaited scalar type, for instance a
  string `"42"` given to a node that awaits an integer.

- When unnecessary array keys are present, for instance mapping an array
  `['foo' => …, 'bar' => …, 'baz' => …]` to an object that needs only `foo` and
  `bar`.

- When permissive types like `mixed` or `object` are encountered.

These limitations can be bypassed by enabling the flexible mode:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->flexible()
    ->mapper();
    ->map('array{foo: int, bar: bool}', [
        'foo' => '42', // Will be cast from `string` to `int`
        'bar' => 'true', // Will be cast from `string` to `bool`
        'baz' => '…', // Will be ignored
    ]);
```

When using this library for a provider application — for instance an API 
endpoint that can be called with a JSON payload — it is recommended to use the
strict mode. This ensures that the consumers of the API provide the exact 
awaited data structure, and prevents unknown values to be passed.

When using this library as a consumer of an external source, it can make sense
to enable the flexible mode. This allows for instance to convert string numeric 
values to integers or to ignore data that is present in the source but not
needed in the application.

**Interface inferring**

It is now mandatory to list all possible class-types that can be inferred by the
mapper. This change is a step towards the library being able to deliver powerful 
new features such as compiling a mapper for better performance.

The existing calls to `MapperBuilder::infer` that could return several 
class-names must now add a signature to the callback. The callbacks that require
no parameter and always return the same class-name can remain unchanged.

For instance:

```php
$builder = (new \CuyZ\Valinor\MapperBuilder())
    // Can remain unchanged
    ->infer(SomeInterface::class, fn () => SomeImplementation::class);
```

```php
$builder = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(
        SomeInterface::class,
        fn (string $type) => match($type) {
            'first' => ImplementationA::class,
            'second' => ImplementationB::class,
            default => throw new DomainException("Unhandled `$type`.")
        }
    )
    // …should be modified with:
    ->infer(
        SomeInterface::class,
        /** @return class-string<ImplementationA|ImplementationB> */
        fn (string $type) => match($type) {
            'first' => ImplementationA::class,
            'second' => ImplementationB::class,
            default => throw new DomainException("Unhandled `$type`.")
        }
    );
```

**Object constructors collision**

All these changes led to a new check that runs on all registered object 
constructors. If a collision is found between several constructors that have the
same signature (the same parameter names), an exception will be thrown.

```php
final class SomeClass
{
    public static function constructorA(string $foo, string $bar): self
    {
        // …
    }

    public static function constructorB(string $foo, string $bar): self
    {
        // …
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        SomeClass::constructorA(...),
        SomeClass::constructorB(...),
    )
    ->mapper();
    ->map(SomeClass::class, [
        'foo' => 'foo',
        'bar' => 'bar',
    ]);

// Exception: A collision was detected […]
```

### ⚠ BREAKING CHANGES

* Handle exhaustive list of interface inferring ([1b0ff3](https://github.com/CuyZ/Valinor/commit/1b0ff39af650f1c5902ee930f49049042842ec08))
* Make mapper more strict and allow flexible mode ([90dc58](https://github.com/CuyZ/Valinor/commit/90dc586018449b15f3b0296241d8cb2d1320d940))

### Features

* Improve cache warmup ([44c5f1](https://github.com/CuyZ/Valinor/commit/44c5f13b70a14cbe1cb2b917acd127d14b8c7d14))

---

## [0.10.0](https://github.com/CuyZ/Valinor/compare/0.9.0...0.10.0) (2022-06-10)

### Notable changes

Documentation is now available at [valinor.cuyz.io](https://valinor.cuyz.io).

### Features

* Support mapping to dates with no time ([e0a529](https://github.com/CuyZ/Valinor/commit/e0a529a7e546c2e3ffb929f819256b90a5f7859a))

### Bug Fixes

* Allow declaring promoted parameter type with `@var` annotation ([d8eb4d](https://github.com/CuyZ/Valinor/commit/d8eb4d830bd0817a5de2d61c8cfa81e7e025064a))
* Allow mapping iterable to shaped array ([628baf](https://github.com/CuyZ/Valinor/commit/628baf1294aaf3ce65bc5af969073604e2005af8))

---

## [0.9.0](https://github.com/CuyZ/Valinor/compare/0.8.0...0.9.0) (2022-05-23)

### Notable changes

**Cache injection and warmup**

The cache feature has been revisited, to give more control to the user on how
and when to use it.

The method `MapperBuilder::withCacheDir()` has been deprecated in favor of a new
method `MapperBuilder::withCache()` which accepts any PSR-16 compliant
implementation.

> **Warning**
>
> These changes lead up to the default cache not being automatically
> registered anymore. If you still want to enable the cache (which you should),
> you will have to explicitly inject it (see below).

A default implementation is provided out of the box, which saves cache entries
into the file system.

When the application runs in a development environment, the cache implementation
should be decorated with `FileWatchingCache`, which will watch the files of the
application and invalidate cache entries when a PHP file is modified by a
developer — preventing the library not behaving as expected when the signature
of a property or a method changes.


The cache can be warmed up, for instance in a pipeline during the build and
deployment of the application — kudos to @boesing for the feature!

> **Note** The cache has to be registered first, otherwise the warmup will end
> up being useless.

```php
$cache = new \CuyZ\Valinor\Cache\FileSystemCache('path/to/cache-directory');

if ($isApplicationInDevelopmentEnvironment) {
    $cache = new \CuyZ\Valinor\Cache\FileWatchingCache($cache);
}

$mapperBuilder = (new \CuyZ\Valinor\MapperBuilder())->withCache($cache);

// During the build:
$mapperBuilder->warmup(SomeClass::class, SomeOtherClass::class);

// In the application:
$mapperBuilder->mapper()->map(SomeClass::class, [/* … */]);
```

---

**Message formatting & translation**

Major changes have been made to the messages being returned in case of a mapping
error: the actual texts are now more accurate and show better information.

> **Warning** 
> 
> The method `NodeMessage::format` has been removed, message formatters should 
> be used instead. If needed, the old behaviour can be retrieved with the
> formatter `PlaceHolderMessageFormatter`, although it is strongly advised to 
> use the new placeholders feature (see below).
> 
> The signature of the method `MessageFormatter::format` has changed as well.

It is now also easier to format the messages, for instance when they need to be
translated. Placeholders can now be used in a message body, and will be replaced 
with useful information.

| Placeholder          | Description                                          |
|----------------------|:-----------------------------------------------------|
| `{message_code}`     | the code of the message                              |
| `{node_name}`        | name of the node to which the message is bound       |
| `{node_path}`        | path of the node to which the message is bound       |
| `{node_type}`        | type of the node to which the message is bound       |
| `{original_value}`   | the source value that was given to the node          |
| `{original_message}` | the original message before being customized         |

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, [/* … */]);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $node = $error->node();
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);

    foreach ($messages as $message) {
        if ($message->code() === 'some_code') {
            $message = $message->withBody('new message / {original_message}');
        }

        echo $message;
    }
}
```

The messages are formatted using the [ICU library], enabling the placeholders to
use advanced syntax to perform proper translations, for instance currency
support.

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())->mapper()->map('int<0, 100>', 1337);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $message = $error->node()->messages()[0];

    if (is_numeric($message->value())) {
        $message = $message->withBody(
            'Invalid amount {original_value, number, currency}'
        );    
    } 

    // Invalid amount: $1,337.00
    echo $message->withLocale('en_US');
    
    // Invalid amount: £1,337.00
    echo $message->withLocale('en_GB');
    
    // Invalid amount: 1 337,00 €
    echo $message->withLocale('fr_FR');
}
```

See [ICU documentation] for more information on available syntax.

> **Warning** If the `intl` extension is not installed, a shim will be
> available to replace the placeholders, but it won't handle advanced syntax as
> described above.

The formatter `TranslationMessageFormatter` can be used to translate the content
of messages.

The library provides a list of all messages that can be returned; this list can
be filled or modified with custom translations.

```php
\CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter::default()
    // Create/override a single entry…
    ->withTranslation('fr', 'some custom message', 'un message personnalisé')
    // …or several entries.
    ->withTranslations([
        'some custom message' => [
            'en' => 'Some custom message',
            'fr' => 'Un message personnalisé',
            'es' => 'Un mensaje personalizado',
        ], 
        'some other message' => [
            // …
        ], 
    ])
    ->format($message);
```

It is possible to join several formatters into one formatter by using the
`AggregateMessageFormatter`. This instance can then easily be injected in a
service that will handle messages.

The formatters will be called in the same order they are given to the aggregate.

```php
(new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\AggregateMessageFormatter(
    new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\LocaleMessageFormatter('fr'),
    new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter([
        // …
    ],
    \CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter::default(),
))->format($message)
```

[ICU library]: https://unicode-org.github.io/icu/

[ICU documentation]: https://unicode-org.github.io/icu/userguide/format_parse/messages/

### ⚠ BREAKING CHANGES

* Improve message customization with formatters ([60a665](https://github.com/CuyZ/Valinor/commit/60a66561413fc0d366cae9acf57ac553bb1a919d))
* Revoke `ObjectBuilder` API access ([11e126](https://github.com/CuyZ/Valinor/commit/11e12624aad2378465122b987e53e76c744d2179))

### Features

* Allow injecting a cache implementation that is used by the mapper ([69ad3f](https://github.com/CuyZ/Valinor/commit/69ad3f47777f2bec4e565b98035f07696cd16d35))
* Extract file watching feature in own cache implementation ([2d70ef](https://github.com/CuyZ/Valinor/commit/2d70efbfbb73dfe02987de82ee7fc5bb38b3e486))
* Improve mapping error messages ([05cf4a](https://github.com/CuyZ/Valinor/commit/05cf4a4a4dc40af735ba12af5bc986ffec015c6c))
* Introduce method to warm the cache up ([ccf09f](https://github.com/CuyZ/Valinor/commit/ccf09fd33433e065ca7ad26cc90e433dc8d1ae84))

### Bug Fixes

* Make interface type match undefined object type ([105eef](https://github.com/CuyZ/Valinor/commit/105eef473821f6f6990a080c5e14a78ed5be24db))

### Other

* Change `InvalidParameterIndex` exception inheritance type ([b75adb](https://github.com/CuyZ/Valinor/commit/b75adb7c7ceba2382cab5f265d93ed46bcfd8f4e))
* Introduce layer for object builder arguments ([48f936](https://github.com/CuyZ/Valinor/commit/48f936275e7f8af937e0740cdbbac0f9557ab4a3))


---

## [0.8.0](https://github.com/CuyZ/Valinor/compare/0.7.1...0.8.0) (2022-05-09)

### Notable changes

**Float values handling**

Allows the usage of float values, as follows:

```php
class Foo
{
    /** @var 404.42|1337.42 */
    public readonly float $value;
}
```

---

**Literal boolean `true` / `false` values handling**

Thanks @danog for this feature!

Allows the usage of boolean values, as follows:

```php
class Foo
{
    /** @var int|false */
    public readonly int|bool $value;
}
```

---

**Class string of union of object handling**

Allows to declare several class names in a `class-string`:

```php
class Foo
{
    /** @var class-string<SomeClass|SomeOtherClass> */
    public readonly string $className;
}
```

---

**Allow `psalm` and `phpstan` prefix in docblocks**

Thanks @boesing for this feature!

The following annotations are now properly handled: `@psalm-param`,
`@phpstan-param`, `@psalm-return` and `@phpstan-return`.

If one of those is found along with a basic `@param` or `@return` annotation, it
will take precedence over the basic value.

### Features

* Allow `psalm` and `phpstan` prefix in docblocks ([64e0a2](https://github.com/CuyZ/Valinor/commit/64e0a2d5ac727062c7c9c45f636081d1065f2bb9))
* Handle class string of union of object ([b7923b](https://github.com/CuyZ/Valinor/commit/b7923bc383f55095683e38a6da760a90df156edd))
* Handle filename in function definition ([0b042b](https://github.com/CuyZ/Valinor/commit/0b042bc495caf30f3a4d6e72547f65dae69282ec))
* Handle float value type ([790df8](https://github.com/CuyZ/Valinor/commit/790df8a3b8e9b608e33086f673372b8dff7775c7))
* Handle literal boolean `true` / `false` types ([afcedf](https://github.com/CuyZ/Valinor/commit/afcedf9e56100e3e69c340172b40cd6deb471f64))
* Introduce composite types ([892f38](https://github.com/CuyZ/Valinor/commit/892f3831c221c93f74edc4ec14b56c281cf2438e))

### Bug Fixes

* Call value altering function only if value is accepted ([2f08e1](https://github.com/CuyZ/Valinor/commit/2f08e1a9b306a1d1ae0e9636049816a9c8bd0b92))
* Handle function definition cache invalidation when file is modified ([511a0d](https://github.com/CuyZ/Valinor/commit/511a0dfee8ab51f9d220df03f063aead603298ba))

### Other

* Add configuration for Composer allowed plugins ([2f310c](https://github.com/CuyZ/Valinor/commit/2f310cf5ab9f36bf98d338a99a536279049d1cef))
* Add Psalm configuration file to `.gitattributes` ([979272](https://github.com/CuyZ/Valinor/commit/9792722e4f17507c73cd243ad3f23053e24278aa))
* Bump dev-dependencies ([844384](https://github.com/CuyZ/Valinor/commit/8443847cb8a7c4810ff8f71cb88487762f1532c1))
* Declare code type in docblocks ([03c84a](https://github.com/CuyZ/Valinor/commit/03c84a1f09b5e4cc567a5640f660a60db860c23c))
* Ignore `Polyfill` coverage ([c08fe5](https://github.com/CuyZ/Valinor/commit/c08fe5a3c53618a9e67d2aaa5f6c9d51f7d482c9))
* Remove `symfony/polyfill-php80` dependency ([368737](https://github.com/CuyZ/Valinor/commit/368737921787a12cd0bd04b5161f36c487e62b64))


---

## [0.7.0](https://github.com/CuyZ/Valinor/compare/0.6.0...0.7.0) (2022-03-24)

### Notable changes

> **Warning** This release introduces a major breaking change that must be
considered before updating

**Constructor registration**

**The automatic named constructor discovery has been disabled**. It is now
mandatory to explicitly register custom constructors that can be used by the
mapper.

This decision was made because of a security issue reported by @Ocramius and
described in advisory [advisory GHSA-xhr8-mpwq-2rr2].

[advisory GHSA-xhr8-mpwq-2rr2]: https://github.com/CuyZ/Valinor/security/advisories/GHSA-5pgm-3j3g-2rc7

As a result, existing code must list all named constructors that were previously
automatically used by the mapper, and registerer them using the
method `MapperBuilder::registerConstructor()`.

The method `MapperBuilder::bind()` has been deprecated in favor of the method
above that should be used instead.

```php
final class SomeClass
{
    public static function namedConstructor(string $foo): self
    {
        // …
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        SomeClass::namedConstructor(...),
        // …or for PHP < 8.1:
        [SomeClass::class, 'namedConstructor'],
    )
    ->mapper()
    ->map(SomeClass::class, [
        // …
    ]);
```

See [documentation](https://github.com/CuyZ/Valinor#custom-constructor) for more
information.

---

**Source builder**

The `Source` class is a new entry point for sources that are not plain array or
iterable. It allows accessing other features like camel-case keys or custom
paths mapping in a convenient way.

It should be used as follows:

```php
$source = \CuyZ\Valinor\Mapper\Source\Source::json($jsonString)
    ->camelCaseKeys()
    ->map([
        'towns' => 'cities',
        'towns.*.label' => 'name',
    ]);

$result = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(SomeClass::class, $source);
```

See [documentation](https://github.com/CuyZ/Valinor#source) for more details
about its usage.

### ⚠ BREAKING CHANGES

* Change `Attributes::ofType` return type to `array` ([1a599b](https://github.com/CuyZ/Valinor/commit/1a599b0bdf5cf07385ed120817f1a4720064e4dc))
* Introduce method to register constructors used during mapping ([ecafba](https://github.com/CuyZ/Valinor/commit/ecafba3b21cd48f38a5e5d2b5b7f97012342536a))

### Features

* Introduce a path-mapping source modifier ([b7a7d2](https://github.com/CuyZ/Valinor/commit/b7a7d22993eb2758f5beb80e58e599df60258bf3))
* Introduce a source builder ([ad5103](https://github.com/CuyZ/Valinor/commit/ad51039cc3f76245eb96fab21a0f2709fcf13bdb))

### Bug Fixes

* Handle numeric key with camel case source key modifier ([b8a18f](https://github.com/CuyZ/Valinor/commit/b8a18feadcbb564d80a34ca004c584c56ac0de04))
* Handle parameter default object value compilation ([fdef93](https://github.com/CuyZ/Valinor/commit/fdef93074c32cf36072446da8a3466a1bf781dbb))
* Handle variadic arguments in callable constructors ([b646cc](https://github.com/CuyZ/Valinor/commit/b646ccecf25ca17ecf8fec4e03bb91748db9527d))
* Properly handle alias types for function reflection ([e5b515](https://github.com/CuyZ/Valinor/commit/e5b5157eaf0b60e10f1060bf22cc4e2bd7d828c7))

### Other

* Add Striker HTML report when running infection ([79c7a4](https://github.com/CuyZ/Valinor/commit/79c7a4563d545d5513f96ec1b5f00abbd50b5881))
* Handle class name in function definition ([e2451d](https://github.com/CuyZ/Valinor/commit/e2451df2c1857a935a1fc2e88c056d6b414ed924))
* Introduce functions container to wrap definition handling ([fd1117](https://github.com/CuyZ/Valinor/commit/fd11177b06c9740c621155c2f635e012c2f8651e))


---

## [0.6.0](https://github.com/CuyZ/Valinor/compare/0.5.0...0.6.0) (2022-02-24)
### ⚠ BREAKING CHANGES

* Improve interface inferring API ([1eb6e6](https://github.com/CuyZ/Valinor/commit/1eb6e6191368453b2c982068a4ceac42681dcbe8))
* Improve object binding API ([6d4270](https://github.com/CuyZ/Valinor/commit/6d427088f7141a860ce89e5874fdcdf3abb528b6))

### Features

* Handle variadic parameters in constructors ([b6b329](https://github.com/CuyZ/Valinor/commit/b6b329663853aec3597041eea3d936186c410621))
* Improve value altering API ([422e6a](https://github.com/CuyZ/Valinor/commit/422e6a8b272ee1955fef904a43c8bf3721b70ae1))
* Introduce a camel case source key modifier ([d94652](https://github.com/CuyZ/Valinor/commit/d9465222f463896f84379f6e6f1cfef016a0470d))
* Introduce function definition repository ([b49ebf](https://github.com/CuyZ/Valinor/commit/b49ebf37be630d4a26e3525ecaedf89fbaa53520))
* Introduce method to get parameter by index ([380961](https://github.com/CuyZ/Valinor/commit/380961247e065155ce2fdd212f8a51bbe82e931e))

### Bug Fixes

* Change license in `composer.json` ([6fdd62](https://github.com/CuyZ/Valinor/commit/6fdd62dfc2fd68b29b6d915e1f3a81f54e5aea51))
* Ensure native mixed types remain valid ([18ccbe](https://github.com/CuyZ/Valinor/commit/18ccbebb9a4484e4efb4db2c5c9405e853578e7d))
* Remove string keys when unpacking variadic parameter values ([cbf4e1](https://github.com/CuyZ/Valinor/commit/cbf4e11154ae428323099db7e689494624f63293))
* Transform exception thrown during object binding into a message ([359e32](https://github.com/CuyZ/Valinor/commit/359e32d03d062610aab4c171a943b28a09cbaf0f))
* Write temporary cache file inside cache subdirectory ([1b80a1](https://github.com/CuyZ/Valinor/commit/1b80a1df9d64e4c835362b34b1dec9b38ad083d3))

### Other

* Check value acceptance in separate node builder ([30d447](https://github.com/CuyZ/Valinor/commit/30d4479aefdeb09c7cea20cb4daea1f2b724871b))
* Narrow union types during node build ([06e9de](https://github.com/CuyZ/Valinor/commit/06e9dedfd8742be8946b57aedc8d03945eae36fc))


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

## [0.4.0](https://github.com/CuyZ/Valinor/compare/0.3.0...0.4.0) (2022-01-07)

### Notable changes

**Allow mapping to any type**

Previously, the method `TreeMapper::map` would allow mapping only to an object.
It is now possible to map to any type handled by the library.

It is for instance possible to map to an array of objects:

```php
$objects = (new MapperBuilder())->mapper()->map(
    'array<' . SomeClass::class . '>',
    [/* … */]
);
```

For simple use-cases, an array shape can be used:

```php
$array = (new MapperBuilder())->mapper()->map(
    'array{foo: string, bar: int}',
    [/* … */]
);

echo $array['foo'];
echo $array['bar'] * 2;
```

This new feature changes the possible behaviour of the mapper, meaning static
analysis tools need help to understand the types correctly. An extension for
PHPStan and a plugin for Psalm are now provided and can be included in a project
to automatically increase the type coverage.

---

**Better handling of messages**

When working with messages, it can sometimes be useful to customize the content
of a message — for instance to translate it.

The helper
class `\CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter` can be
used to provide a list of new formats. It can be instantiated with an array
where each key represents either:

- The code of the message to be replaced
- The content of the message to be replaced
- The class name of the message to be replaced

If none of those is found, the content of the message will stay unchanged unless
a default one is given to the class.

If one of these keys is found, the array entry will be used to replace the
content of the message. This entry can be either a plain text or a callable that
takes the message as a parameter and returns a string; it is for instance
advised to use a callable in cases where a translation service is used — to
avoid useless greedy operations.

In any case, the content can contain placeholders that will automatically be
replaced by, in order:

1. The original code of the message
2. The original content of the message
3. A string representation of the node type
4. The name of the node
5. The path of the node

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, [/* … */]);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $node = $error->node();
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);

    $formatter = (new MessageMapFormatter([
        // Will match if the given message has this exact code
        'some_code' => 'new content / previous code was: %1$s',
    
        // Will match if the given message has this exact content
        'Some message content' => 'new content / previous message: %2$s',
    
        // Will match if the given message is an instance of `SomeError`
        SomeError::class => '
            - Original code of the message: %1$s
            - Original content of the message: %2$s
            - Node type: %3$s
            - Node name: %4$s
            - Node path: %5$s
        ',
    
        // A callback can be used to get access to the message instance
        OtherError::class => function (NodeMessage $message): string {
            if ((string)$message->type() === 'string|int') {
                // …
            }
    
            return 'Some message content';
        },
    
        // For greedy operation, it is advised to use a lazy-callback
        'bar' => fn () => $this->translator->translate('foo.bar'),
    ]))
        ->defaultsTo('some default message')
        // …or…
        ->defaultsTo(fn () => $this->translator->translate('default_message'));

    foreach ($messages as $message) {
        echo $formatter->format($message);    
    }
}
```

---

**Automatic union of objects inferring during mapping**

When the mapper needs to map a source to a union of objects, it will try to
guess which object it will map to, based on the needed arguments of the objects,
and the values contained in the source.

```php
final class UnionOfObjects
{
    public readonly SomeFooObject|SomeBarObject $object;
}

final class SomeFooObject
{
    public readonly string $foo;
}

final class SomeBarObject
{
    public readonly string $bar;
}

// Will map to an instance of `SomeFooObject`
(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(UnionOfObjects::class, ['foo' => 'foo']);

// Will map to an instance of `SomeBarObject`
(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(UnionOfObjects::class, ['bar' => 'bar']);
```

### ⚠ BREAKING CHANGES

* Add access to root node when error occurs during mapping ([54f608](https://github.com/CuyZ/Valinor/commit/54f608e5b1a4bbd508246c063a3e79df48c1ddeb))
* Allow mapping to any type ([b2e810](https://github.com/CuyZ/Valinor/commit/b2e810e3ce997181b7cfcc96d2bccb9c56a5bdd8))
* Allow object builder to yield arguments without source ([8a7414](https://github.com/CuyZ/Valinor/commit/8a74147d4c1f78696049c2dfb9acf979ef6e4689))
* Wrap node messages in proper class ([a805ba](https://github.com/CuyZ/Valinor/commit/a805ba0442f9ac8dd6b2499426ffa6fcb190d4be))

### Features

* Introduce automatic union of objects inferring during mapping ([79d7c2](https://github.com/CuyZ/Valinor/commit/79d7c266ecabc069b11120338ae1e62a8f3dca97))
* Introduce helper class `MessageMapFormatter` ([ddf69e](https://github.com/CuyZ/Valinor/commit/ddf69efaaa4eeab87126bdc7f43b92d5faa06f46))
* Introduce helper class `MessagesFlattener` ([a97b40](https://github.com/CuyZ/Valinor/commit/a97b406154fa5bccc2f874634ed24af69401cd4a))
* Introduce helper `NodeTraverser` for recursive operations on nodes ([cc1bc6](https://github.com/CuyZ/Valinor/commit/cc1bc66bbece7f79dfec093e33e96d3e31bca4e9))

### Bug Fixes

* Handle nested attributes compilation ([d2795b](https://github.com/CuyZ/Valinor/commit/d2795bc6b9ee53f9896889cabc1006be2586b008))
* Treat forbidden mixed type as invalid type ([36bd36](https://github.com/CuyZ/Valinor/commit/36bd3638c8e48d2c43d247991b8d2055e8ab56bb))
* Treat union type resolving error as message ([e834cd](https://github.com/CuyZ/Valinor/commit/e834cdc5d3384d4295582f8d602b5da5f656e83a))
* Use locked package versions for quality assurance workflow ([626f13](https://github.com/CuyZ/Valinor/commit/626f135eee5b4954845e954d567babd58f6ca51b))

### Other

* Ignore changelog configuration file in git export ([85a6a4](https://github.com/CuyZ/Valinor/commit/85a6a49ce20b96f2e023a7e824b8085ea198fd39))
* Raise PHPStan version ([0144bf](https://github.com/CuyZ/Valinor/commit/0144bf084a4883ecbdb65155a05b2ea90e7fca61))


---

## [0.3.0](https://github.com/CuyZ/Valinor/compare/0.2.0...0.3.0) (2021-12-18)
### Features

* Handle common database datetime formats (#40) ([179ba3](https://github.com/CuyZ/Valinor/commit/179ba3df299be62b0eac24a80e9d4d56d0bb5074))

### Other

* Change Composer scripts calls ([0b507c](https://github.com/CuyZ/Valinor/commit/0b507c9b330b76e29319dd5933aa5760df3e3c8d))
* Raise version of `friendsofphp/php-cs-fixer` ([e5ccbe](https://github.com/CuyZ/Valinor/commit/e5ccbe201b8767066e0e0510e606f9492f3270f1))


---

## [0.2.0](https://github.com/CuyZ/Valinor/compare/0.1.1...0.2.0) (2021-12-07)
### Features

* Handle integer range type ([9f99a2](https://github.com/CuyZ/Valinor/commit/9f99a2a1eff3a1a120a9fa80ecb7045eeafdbfd3))
* Handle local type aliasing in class definition ([56142d](https://github.com/CuyZ/Valinor/commit/56142dea5b7c2eb7fdd591ede2f2bd0a1dd3e7b4))
* Handle type alias import in class definition ([fa3ce5](https://github.com/CuyZ/Valinor/commit/fa3ce50dfb069a3e908d5abdb59bd70b3b7d3a90))

### Bug Fixes

* Do not accept shaped array with excessive key(s) ([5a578e](https://github.com/CuyZ/Valinor/commit/5a578ea4c26e82fe2bea0bbdba821ff2cd2de03d))
* Handle integer value match properly ([9ee2cc](https://github.com/CuyZ/Valinor/commit/9ee2cc471ebded7b0b4416d1ed245752090f32be))

### Other

* Delete commented code ([4f5612](https://github.com/CuyZ/Valinor/commit/4f561290b1ba71dc15f1b2806d2ea929429d5910))
* Move exceptions to more specific folder ([185edf](https://github.com/CuyZ/Valinor/commit/185edf60534391475da4d90a4fec4bab58c9e1c8))
* Rename `GenericAssignerLexer` to `TypeAliasLexer` ([680941](https://github.com/CuyZ/Valinor/commit/680941687b5e0488eebbfc4372162c25fab34bcb))
* Use `marcocesarato/php-conventional-changelog` for changelog ([178aa9](https://github.com/CuyZ/Valinor/commit/178aa97687bd1b2de1c21a57b20476629d2de748))


---

## [0.1.1](https://github.com/CuyZ/Valinor/compare/0.1.0...0.1.1) (2021-12-01)
### ⚠ BREAKING CHANGES

* Change license from GPL 3 to MIT ([a77b28](https://github.com/CuyZ/Valinor/commit/a77b28c5c224f6cb1232ac17de0002bff8e09ad1))

### Features

* Handle multiline type declaration ([d99c59](https://github.com/CuyZ/Valinor/commit/d99c59dfb568950be3abda683051ac9b358af78e))

### Bug Fixes

* Filter type symbols with strict string comparison ([6cdea3](https://github.com/CuyZ/Valinor/commit/6cdea31bc29e2bdbb2574ee0678e71cb42c4761b))
* Handle correctly iterable source during mapping ([dd4624](https://github.com/CuyZ/Valinor/commit/dd4624c5e0376cf1c590117dcad10c659a614701))
* Handle shaped array integer key ([5561d0](https://github.com/CuyZ/Valinor/commit/5561d018abb5b237c7cb8385c9a3032c86711738))
* Resolve single/double quotes when parsing doc-block type ([1c628b](https://github.com/CuyZ/Valinor/commit/1c628b66755497a704ae3333abc8782bac8ee02a))

### Other

* Change PHPStan stub file extension ([8fc6af](https://github.com/CuyZ/Valinor/commit/8fc6af283cebe1b317b9107d4741a5f2bddea095))
* Delete unwanted code ([e3e169](https://github.com/CuyZ/Valinor/commit/e3e169fb3c05a46a23cdf7c0c989b5c637a0bd6d))
* Syntax highlight stub files (#9) ([9ea95f](https://github.com/CuyZ/Valinor/commit/9ea95f43f3b8b750c6c147d25cc77a602a696172))
* Use composer runtime API ([1f754a](https://github.com/CuyZ/Valinor/commit/1f754a7e77c9cf3faffccddfdc5179ea1f12840b))


---
