# Changelog 1.13.0 — 2nd of September 2024

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.13.0

## Notable changes

**Microseconds support for timestamp format**

Prior to this patch, this would require a custom constructor in the form of:

```php
static fn(float | int $timestamp): DateTimeImmutable => new
    DateTimeImmutable(sprintf("@%d", $timestamp)),
```

This bypasses the datetime format support of Valinor entirely. This is required
because the library does not support floats as valid `DateTimeInterface` input
values.

This commit adds support for floats and registers `timestamp.microseconds`
(`U.u`) as a valid default format.

**Support for `value-of<BackedEnum>` type**

This type can be used as follows:

```php
enum Suit: string
{
    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';
}

$suit = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map('value-of<Suit>', 'D');

// $suit === 'D'
```

**Object constructors parameters types inferring improvements**

The collision system that checks object constructors parameters types is now way
more clever, as it no longer checks for parameters' names only. Types are now
also checked, and only true collision will be detected, for instance when two
constructors share a parameter with the same name and type.

Note that when two parameters share the same name, the following type priority
operates:

1. Non-scalar type
2. Integer type
3. Float type
4. String type
5. Boolean type

With this change, the code below is now valid:

```php
final readonly class Money
{
    private function __construct(
        public int $value,
    ) {}

    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public static function fromString(string $value): self
    {
        if (! preg_match('/^\d+€$/', $value)) {
            throw new \InvalidArgumentException('Invalid money format');
        }

        return new self((int)rtrim($value, '€'));
    }
}

$mapper = (new \CuyZ\Valinor\MapperBuilder())->mapper();

$mapper->map(Money::class, 42); // ✅
$mapper->map(Money::class, '42€'); // ✅
```

### Features

* Add microseconds support to timestamp format ([02bd2e](https://github.com/CuyZ/Valinor/commit/02bd2e5e0f0e7d4daf234852464085bcdd1a0eb2))
* Add support for `value-of<BackedEnum>` type ([b1017c](https://github.com/CuyZ/Valinor/commit/b1017ce55729f0698c7629d57a3d3a30c0f9bff3))
* Improve object constructors parameters types inferring ([2150dc](https://github.com/CuyZ/Valinor/commit/2150dcad4ce821bfe36c3718346ccc412e37832a))

### Bug Fixes

* Allow any constant in class constant type ([694275](https://github.com/CuyZ/Valinor/commit/6942755865f91c80af8ea97fde2faa390478a6b8))
* Allow docblock for transformer callable type ([69e0e3](https://github.com/CuyZ/Valinor/commit/69e0e3a5f1de6a5eedcfa4125d8639be91f0c303))
* Do not override invalid variadic parameter type ([c5860f](https://github.com/CuyZ/Valinor/commit/c5860f0e5b3f59f49900bfbb20ca4493916eca7a))
* Handle interface generics ([40e6fa](https://github.com/CuyZ/Valinor/commit/40e6fa340819961068b8be178e312a99c06cede2))
* Handle iterable objects as iterable during normalization ([436e3c](https://github.com/CuyZ/Valinor/commit/436e3c25532d5cf396b00354ec5459e812c2953e))
* Properly format empty object with JSON normalizer ([ba22b5](https://github.com/CuyZ/Valinor/commit/ba22b5233e80f0ffbbe9591a5099b9dd62715eb8))
* Properly handle nested local type aliases ([127839](https://github.com/CuyZ/Valinor/commit/1278392757a4e9dc9eee2ab642c5700e83ccf982))

### Other

* Exclude unneeded attributes in class/function definitions ([1803d0](https://github.com/CuyZ/Valinor/commit/1803d094f08b256c64535f4f86e32ab35a07bbf1))
* Improve mapping performance for nullable union type ([6fad94](https://github.com/CuyZ/Valinor/commit/6fad94a46785dfb853c11c241e3f60bcf6a85ede))
* Move "float type accepting integer value" logic in `Shell` ([047953](https://github.com/CuyZ/Valinor/commit/0479532fbc96fca35dcbfb4c1f5a9ef63e7625c5))
* Move setting values in shell ([84b1ff](https://github.com/CuyZ/Valinor/commit/84b1ffbc8190a709d752a9882f71f6f419ad0434))
* Reorganize type resolver services ([86fb7b](https://github.com/CuyZ/Valinor/commit/86fb7b6303b15b54da6ac02ca8a7008b23c8bcff))
