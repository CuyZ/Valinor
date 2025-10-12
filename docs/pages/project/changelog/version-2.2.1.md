# Changelog 2.2.1 — 13th of October 2025

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.2.1

## ⚠️ Important changes ⚠️

This release contains a lot of internal refactorings that were needed to fix an
important bug regarding converters. Although we made our best to provide a
stable release, bugs can have slipped through the cracks. If that's the case,
please [open an issue](https://github.com/CuyZ/Valinor/issues/new) describing
the issue and we will try to fix it as soon as possible. 

⚠️ This fix is not backward-compatible in some cases, which are explained below.
If you use mapper converters in your application, you should definitely read the
following changes carefully.

---

The commit [d9e3cf0] is the result of a long journey whose goal was to fix a 
very upsetting bug that would make mapper converters being called when they
shouldn't be. This could result in unexpected behaviors and could even lead to
invalid data being mapped.

Take the following example below:

We register a converter that will return null if the string length is lower
than 5. For this converter to be called, the target type should match the 
`string|null` type, because that is what the converter can return.

In this example, we want to map a value to `string`, which is not matched by the
converter return type because it does not contain `null`. This means that the
converter should never be called, because it could return an invalid value
(`null` will never be a valid `string`).

```php
 (new \CuyZ\Valinor\MapperBuilder())
    ->registerConverter(
        // If the string length is lower than 5, we return `null`
        fn (string $val): ?string => strlen($val) < 5 ? null : $val
    )
    ->mapper()
    ->map('string', 'foo');
```

Before this commit, the converter would be called and return `null`, which would
raise an unexpected error:

> An error occurred at path *root*: value null is not a valid string.

This error was caused by the following line:

```php
if (! $shell->type->matches($converter->returnType)) {
    continue;
}
```

It should have been:

```php
if (! $converter->returnType->matches($shell->type)) {
    continue;
}
```

Easy fix, isn't it?

Well… actually no. Because changing this completely modifies the behavior of the
converters, and the library is now missing a lot of information to properly
infer the return type of the converter.

In some cases this change was enough, but in some more complex cases we now
would need more information.

For instance, let's take the `CamelCaseKeys` example as it was written in the
documentation before this commit:

```php
final class CamelCaseKeys
{
    /**
     * @param array<mixed> $value
     * @param callable(array<mixed>): object $next
     */
    public function map(array $value, callable $next): object { … }
}
```

There is a big issue in the types signature of this converter: the `object`
return type means that the converter can return *anything*, as long as this is
an object. This breaks the type matching contract and the converter should never
be called. But it was.

This is the new way of writing this converter:

```php
final class CamelCaseKeys
{
    /**
     * @template T of object
     * @param array<mixed> $value
     * @param callable(array<mixed>): T $next
     * @return T
     */
    public function map(array $value, callable $next): object { … }
```

Now, the type matching contract is respected because of the `@template`
annotation, and the converter is called when mapping to any object.

To be able to properly infer the return type of the converter, we needed to:

1. Be able to understand `@template` annotations inside functions
2. Be able to statically infer the generics using these annotations
3. Assign the inferred generics to the whole converter
4. Let the system call the converter pipeline properly

This was a *huge* amount of work, which required several small changes during
the last month, as well as [b7f3e5f] and [d9e3cf0]. A lot of work for an error
in a single line of code, right? T_T

The good news is: the library is now more powerful than ever, as it is now able
to statically infer generic types, which could bring new possibilities in the 
future.

Now the bad news is: this commit can break backwards compatibility promise in 
some cases. But as this is still a (huge) bug fix, we will not release a new 
major version, although it can break some existing code. Instead, converters 
should be adapted to use proper type signatures.

To help with that, here are the list of the diff that should be applied to
converter examples that were written in the documentation:

**CamelCaseKeys**

```diff
#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class CamelCaseKeys
{
    /**
+    * @template T of object
     * @param array<mixed> $value
-    * @param callable(array<mixed>): object $next
+    * @param callable(array<mixed>): T $next
+    * @return T
     */
    public function map(array $value, callable $next): object
    {
        …
    }
}
```

**RenameKeys**

```diff
#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class RenameKeys
{
    public function __construct(
        /** @var non-empty-array<non-empty-string, non-empty-string> */
        private array $mapping,
    ) {}

    /**
+    * @template T of object
     * @param array<mixed> $value
-    * @param callable(array<mixed>): object $next
+    * @param callable(array<mixed>): T $next
+    * @return T
     */
    public function map(array $value, callable $next): object
    {
        …
    }
}
```

**Explode**

```diff
#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Explode
{
    public function __construct(
        /** @var non-empty-string */
        private string $separator,
    ) {}

    /**
-    * @return array<mixed>
+    * @return list<string>
     */
    public function map(string $value): array
    {
        return explode($this->separator, $value);
    }
}
```

**ArrayToList**

```diff
#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayToList
{
    /**
     * @template T
-    * @param array<mixed> $value
+    * @param non-empty-array<T> $value
-    * @return list<mixed>
+    * @return non-empty-list<T>
     */
    public function map(array $value): array
    {
        return array_values($value);
    }
}
```

**JsonDecode**

```diff
#[\CuyZ\Valinor\Mapper\AsConverter]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class JsonDecode
{
     /**
+    * @template T
-    * @param callable(mixed): mixed $next
+    * @param callable(mixed): T $next
+    * @return T
     */
    public function map(string $value, callable $next): mixed
    {
        $decoded = json_decode($value, associative: true);

        return $next($decoded);
    }
}
```

### Bug Fixes

* Make iterable type not match array types ([27f2e3](https://github.com/CuyZ/Valinor/commit/27f2e3a35ccfd42ddfc951e09145d29322989154))
* Prevent undefined object type to match invalid types ([4ae98a](https://github.com/CuyZ/Valinor/commit/4ae98aa95d9d6a6bda5b5ab9279f7ec783575843))
* Properly handle union and array-key types matching ([71787a](https://github.com/CuyZ/Valinor/commit/71787a26c304b64b2a17e5fc9b291ab8533b0f1b))
* Use converter only if its return type matches the current node ([d9e3cf](https://github.com/CuyZ/Valinor/commit/d9e3cf06d2200a9c715b50b41d1d0dbf3a03b222))

### Internal

* Detect converter argument value using native functions ([81b4e5](https://github.com/CuyZ/Valinor/commit/81b4e5217c7b02061c0283f55963776c7559899f))
* Refactor class and interface mapping process ([ab1350](https://github.com/CuyZ/Valinor/commit/ab13502de8c7cf0081a85ca2c23e9b8fd2d9a5b6))
* Refactor definition type assignments to handle generic types ([b7f3e5](https://github.com/CuyZ/Valinor/commit/b7f3e5f3b023fcb94c8bc83ee722a90276eaf9d4))
* Refactor shell responsibilities and node builders API ([63624c](https://github.com/CuyZ/Valinor/commit/63624c019d6da59274a0b3f01ef52fd25a635567))
* Remove exception code timestamps from codebase ([460bb2](https://github.com/CuyZ/Valinor/commit/460bb2b6a8ccfb6448764f29220323f90f154a10))
* Use `INF` constant to detect default converter value ([72079b](https://github.com/CuyZ/Valinor/commit/72079b5ee220263b2e50db4a7f82cf5d01122060))

### Other

* Enhance `callable` type parsing ([2563a3](https://github.com/CuyZ/Valinor/commit/2563a373e677a38490492d6c92c71159098abd82))

[b7f3e5f]: https://github.com/CuyZ/Valinor/commit/b7f3e5f
[d9e3cf0]: https://github.com/CuyZ/Valinor/commit/d9e3cf0
