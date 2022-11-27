# Changelog 0.17.0 — 8th of November 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.17.0

## Notable changes

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

## Features

* Add support for `strict-array` type ([d456eb](https://github.com/CuyZ/Valinor/commit/d456ebe003b1335701c577c256be5df1ad4445e7))
* Introduce new callback message formatter ([93f898](https://github.com/CuyZ/Valinor/commit/93f898c9a68cea8de3615b20271e93abde83aff3))
* Introduce new helper class to list messages ([513827](https://github.com/CuyZ/Valinor/commit/513827d5b39b7d122be958f56cf83eb7e743edaa))
* Split mapper flexible mode in three distinct modes ([549e5f](https://github.com/CuyZ/Valinor/commit/549e5fe183be2191501af8f98df00a84f97ee990))

## Bug Fixes

* Allow missing and null value for array node in flexible mode ([034f1c](https://github.com/CuyZ/Valinor/commit/034f1c51e1d2416624eba99a26da057af3159dda))
* Allow missing value for shaped array nullable node in flexible mode ([08fb0e](https://github.com/CuyZ/Valinor/commit/08fb0e17ba952d42fafade5bea0e83461b02ae6e))
* Handle scalar value casting in union types only in flexible mode ([752ad9](https://github.com/CuyZ/Valinor/commit/752ad9d12eabbb5cd3a8300620838243e7e3335b))

## Other

* Do not use `uniqid()` ([b81847](https://github.com/CuyZ/Valinor/commit/b81847839df64006576f54d3bc3cfeadc425f74b))
* Transform missing source value to `null` in flexible mode ([92a41a](https://github.com/CuyZ/Valinor/commit/92a41a1564f32eb39df7f6c20cbead088feebe2b))
