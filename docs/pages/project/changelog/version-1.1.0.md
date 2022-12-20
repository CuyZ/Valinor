# Changelog 1.1.0 — 20th of December 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.1.0

## Notable changes

**Handle class generic types inheritance**

It is now possible to use the `@extends` tag (already handled by PHPStan and
Psalm) to declare the type of a parent class generic. This logic is recursively
applied to all parents.

```php
/**
 * @template FirstTemplate
 * @template SecondTemplate
 */
abstract class FirstClassWithGenerics
{
    /** @var FirstTemplate */
    public $valueA;

    /** @var SecondTemplate */
    public $valueB;
}

/**
 * @template FirstTemplate
 * @extends FirstClassWithGenerics<FirstTemplate, int>
 */
abstract class SecondClassWithGenerics extends FirstClassWithGenerics
{
    /** @var FirstTemplate */
    public $valueC;
}

/**
 * @extends SecondClassWithGenerics<string>
 */
final class ChildClass extends SecondClassWithGenerics
{
}

$object = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(ChildClass::class, [
        'valueA' => 'foo',
        'valueB' => 1337,
        'valueC' => 'bar',
    ]);

echo $object->valueA; // 'foo'
echo $object->valueB; // 1337
echo $object->valueC; // 'bar'
```

**Added support for class inferring**

It is now possible to infer abstract or parent classes the same way it
can be done for interfaces.

Example with an abstract class:

```php
abstract class SomeAbstractClass
{
    public string $foo;

    public string $bar;
}

final class SomeChildClass extends SomeAbstractClass
{
    public string $baz;
}

$result = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(
        SomeAbstractClass::class,
        fn () => SomeChildClass::class
    )
    ->mapper()
    ->map(SomeAbstractClass::class, [
        'foo' => 'foo',
        'bar' => 'bar',
        'baz' => 'baz',
    ]);

assert($result instanceof SomeChildClass);
assert($result->foo === 'foo');
assert($result->bar === 'bar');
assert($result->baz === 'baz');
```

## Features

* Add support for class inferring ([5a90ad](https://github.com/CuyZ/Valinor/commit/5a90ad49504d72a21d0387a35b9514571d463bdd))
* Handle class generic types inheritance ([6506b7](https://github.com/CuyZ/Valinor/commit/6506b73f7520450990f6c6ce166af6fc315249b4))

## Bug Fixes

* Handle `object` return type in PHPStan extension ([201728](https://github.com/CuyZ/Valinor/commit/201728a09c400ed3b6e55726ea7bf41fb3407497))
* Import plugin class file in PHPStan configuration ([58d540](https://github.com/CuyZ/Valinor/commit/58d54013716dc67ac27fe2117f481de496c1b148))
* Keep nested errors when superfluous keys are detected ([813b3b](https://github.com/CuyZ/Valinor/commit/813b3b4bdf62b739e0c3b5558327ddae9cce6bcc))

## Other

* Adapt code with PHP 8.0 syntax ([3fac3e](https://github.com/CuyZ/Valinor/commit/3fac3e54089b5d37aab07c24aaccbfa7f8dfd76e))
* Add `isAbstract` flag in class definition ([ad0c06](https://github.com/CuyZ/Valinor/commit/ad0c06b848920791f6eaf19e2aae09c610147ccb))
* Add `isFinal` flag in class definition ([25da31](https://github.com/CuyZ/Valinor/commit/25da31e4314eca800b52df933825716fba56b5bf))
* Enhance `TreeMapper::map()` return type signature ([dc32d3](https://github.com/CuyZ/Valinor/commit/dc32d3e1d5b26ea175a01c88eba14549afc53abe))
* Improve return type signature for `TreeMapper` ([c8f362](https://github.com/CuyZ/Valinor/commit/c8f36217dbf242c6dd5917a54655b75bccc0bbf3))
* Prevent multiple cache round-trip ([13b620](https://github.com/CuyZ/Valinor/commit/13b6205c77a3aaac9d2d24b4045ea797afe8a68e))
