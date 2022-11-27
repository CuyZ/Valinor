# Changelog 0.12.0 — 10th of July 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.12.0

## Notable changes

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

## ⚠ BREAKING CHANGES

* Filter userland exceptions to hide potential sensible data ([6ce1a4](https://github.com/CuyZ/Valinor/commit/6ce1a439adb1f6ee7e771fe02d454aa91e7b320f))
* Refactor tree node API ([d3b1dc](https://github.com/CuyZ/Valinor/commit/d3b1dcb64ec561cdedffe5ca779341fc9452a858))
* Remove API access from several parts of library ([316d91](https://github.com/CuyZ/Valinor/commit/316d91910d289780a7b791f17b958eae264a6296))
* Remove node visitor feature ([63c87a](https://github.com/CuyZ/Valinor/commit/63c87a2cc4c28546f28d51998a93fe89f0885535))

## Bug Fixes

* Handle inferring methods with same names properly ([dc45dd](https://github.com/CuyZ/Valinor/commit/dc45dd8ac5ab1126a362350dbc5292a421254d54))
* Process invalid type default value as unresolvable type ([7c9ac1](https://github.com/CuyZ/Valinor/commit/7c9ac1dd6d518e5e5f0fc02ee172b12084082d1d))
* Properly display unresolvable type ([3020db](https://github.com/CuyZ/Valinor/commit/3020db20bfa8322e3cb198487851bb5d43ea9894))

## Other

* Ignore `.idea` folder ([84ead0](https://github.com/CuyZ/Valinor/commit/84ead04f84118d18ad0c557db909b0cd10b65252))
