# Changelog 1.3.0 — 8th of February 2023

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.3.0

## Notable changes

**Handle custom enum constructors registration**

It is now possible to register custom constructors for enum, the same way it
could be done for classes.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        // Allow the native constructor to be used
        SomeEnum::class,

        // Register a named constructor
        SomeEnum::fromMatrix(...)
    )
    ->mapper()
    ->map(SomeEnum::class, [
        'type' => 'FOO',
        'number' => 'BAR',
    ]);

enum SomeEnum: string
{
    case CASE_A = 'FOO_VALUE_1';
    case CASE_B = 'FOO_VALUE_2';
    case CASE_C = 'BAR_VALUE_1';
    case CASE_D = 'BAR_VALUE_2';

    /**
     * @param 'FOO'|'BAR' $type
     * @param int<1, 2> $number
     * /
    public static function fromMatrix(string $type, int $number): self
    {
        return self::from("{$type}_VALUE_{$number}");
    }
}
```

An enum constructor can be for a specific pattern:

```php
enum SomeEnum
{
    case FOO;
    case BAR;
    case BAZ;
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        /**
         * This constructor will be called only when pattern
         * `SomeEnum::BA*` is requested during mapping.
         *
         * @return SomeEnum::BA*
         */
        fn (string $value): SomeEnum => /* Some custom domain logic */
    )
    ->mapper()
    ->map(SomeEnum::class . '::BA*', 'some custom value');
```

Note that this commit required heavy refactoring work, leading to a regression
for union types containing enums and other types. As these cases are considered
marginal, this change is considered non-breaking.

## Features

* Handle custom enum constructors registration ([217e12](https://github.com/CuyZ/Valinor/commit/217e12047440f7ded43c502b8fea1246dd23f2c3))

## Other

* Handle enum type as class type ([5a3caf](https://github.com/CuyZ/Valinor/commit/5a3caf4b246315715c51802e88d939b7c17a27e3))
