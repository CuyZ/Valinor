# Type reference

To prevent conflicts or duplication of the type annotations, this library tries
to handle most of the type annotations that are accepted by [PHPStan] and
[Psalm].

!!! tip

    When a property, parameter or return type uses a PHPStan or Psalm syntax
    that the library cannot parse yet, for instance a conditional type like
    `($a is 1 ? int : null)`, the dedicated `@valinor-var`, `@valinor-param` and
    `@valinor-return` annotations can be used to give the library a type it
    understands. They take precedence over every other annotation, so the static
    analysis tools keep using their own type while the library uses the
    override:
    
    ```php
    final class SomeClass
    {
        /**
         * @phpstan-param ($a is 1 ? int : null) $b
         * @valinor-param int|null $b
         */
        public function __construct(
            public readonly int $a,
            public readonly ?int $b,
        ) {}
    }
    ```

## Scalar

```php
final readonly class SomeClass
{
    public function __construct(
        public bool $boolean,

        public float $float,

        public int $integer,

        /** @var positive-int */
        public int $positiveInteger,

        /** @var negative-int */
        public int $negativeInteger,

        /** @var non-positive-int */
        public int $nonPositiveInteger,

        /** @var non-negative-int */
        public int $nonNegativeInteger,

        /** @var int<-42, 1337> */
        public int $integerRange,

        /** @var int<min, 0> */
        public int $integerRangeWithMinRange,

        /** @var int<0, max> */
        public int $integerRangeWithMaxRange,

        public string $string,
        
        /** @var non-empty-string */
        public string $nonEmptyString,
        
        /** @var numeric-string */
        public string $numericString,

        /** @var class-string */
        public string $classString,

        /** @var class-string<SomeInterface> */
        public string $classStringOfAnInterface,
        
        /** @var value-of<SomeEnum> */
        public string $valueOfEnum,
        
        /** @var scalar */
        public bool|string|int|float $scalar,
    ) {}
}
```

## Object

```php
final readonly class SomeClass
{
    public function __construct(
        public SomeClass $class,

        public DateTimeInterface $interface,

        /** @var SomeInterface&AnotherInterface */
        public object $intersection,

        /** @var SomeCollection<SomeClass> */
        public SomeCollection $classWithGeneric,
    ) {}
}

/**
 * @template T of object 
 */
final readonly class SomeCollection
{
    public function __construct(
        /** @var array<T> */
        public array $objects,
    ) {}
}
```

## Generics

A class can declare template types with the `@template` annotation; they are
filled in with concrete types when the class is referenced. A template can be
bound to a type with `of`, and can declare a default type with `=`.

A template that declares a default type may be omitted when the class is
referenced, in which case the default type is used. Templates that do not
declare one must always be filled in.

```php
final readonly class SomeUser
{
    public function __construct(
        public string $name,
    ) {}
}

/**
 * @template TValue
 * @template TMeta of array<string, mixed> = array<string, string>
 */
final readonly class SomePage
{
    public function __construct(
        /** @var list<TValue> */
        public array $items,

        /** @var TMeta */
        public array $meta,
    ) {}
}

final readonly class SomeClass
{
    public function __construct(
        // `TMeta` is not filled in, its default type is used
        /** @var SomePage<SomeUser> */
        public SomePage $pageWithDefaultMeta,

        // `TMeta` is filled in, overriding its default type
        /** @var SomePage<SomeUser, array{cursor: int}> */
        public SomePage $pageWithCursorMeta,
    ) {}
}
```

Templates declaring a default type must come last: a template without a default
type cannot be declared after one that has a default type. A default type must
also match the bound declared with `of`, when there is one.

!!! note

    A default type is what makes it possible to add a template to a class that
    is already referenced elsewhere: the existing references, which do not fill
    the new template in, keep resolving to its default type and can be made
    more precise later on.

## Array & lists

```php
final readonly class SomeClass
{
    public function __construct(
        /** @var string[] */
        public array $simpleArray,

        /** @var array<string> */
        public array $arrayOfStrings,

        /** @var array<string, SomeClass> */
        public array $arrayOfClassWithStringKeys,

        /** @var array<int, SomeClass> */
        public array $arrayOfClassWithIntegerKeys,

        /** @var array<non-empty-string, string> */
        public array $arrayOfClassWithNonEmptyStringKeys,
        
        /** @var array<'foo'|'bar', string> */
        public array $arrayOfClassWithStringValueKeys,
        
        /** @var array<42|1337, string> */
        public array $arrayOfClassWithIntegerValueKeys,
        
        /** @var array<positive-int, string> */
        public array $arrayOfClassWithPositiveIntegerValueKeys,

        /** @var non-empty-array<string> */
        public array $nonEmptyArrayOfStrings,

        /** @var non-empty-array<string, SomeClass> */
        public array $nonEmptyArrayWithStringKeys,
        
        /** @var list<string> */
        public array $listOfStrings,
        
        /** @var non-empty-list<string> */
        public array $nonEmptyListOfStrings,

        /** @var array{foo: string, bar: int} */
        public array $shapedArray,

        /** @var array{foo: string, bar?: int} */
        public array $shapedArrayWithOptionalElement,

        /** @var array{string, bar: int} */
        public array $shapedArrayWithUndefinedKey,

        /** @var array{foo: string, ...} */
        public array $unsealedShapedArray,
        
        /** @var array{foo: string, ...array<string>} */
        public array $unsealedShapedArrayWithExplicitType,
        
        /** @var array{foo: string, ...array<int, string>} */
        public array $unsealedShapedArrayWithExplicitKeyAndType,

        /** @var array{foo: string, ...<string>} */
        public array $unsealedShapedArrayWithShorthandType,

        /** @var array{foo: string, ...<int, string>} */
        public array $unsealedShapedArrayWithShorthandKeyAndType,

        /** @var list{string, int, float} */
        public array $shapedList,

        /** @var list{0: string, 1: int} */
        public array $shapedListWithExplicitKeys,

        /** @var list{0: string, 1?: int} */
        public array $shapedListWithOptionalElement,

        /** @var list{string, int, ...} */
        public array $unsealedShapedList,

        /** @var list{string, int, ...list<float>} */
        public array $unsealedShapedListWithExplicitType,

        /** @var list{string, ...<float>} */
        public array $unsealedShapedListWithShorthandType,
    ) {}
}
```

## Union

```php
final readonly class SomeClass
{
    public function __construct(
        public int|string $simpleUnion,
        
        /** @var class-string<SomeInterface|AnotherInterface> */
        public string $unionOfClassString,
        
        /** @var array<SomeInterface|AnotherInterface> */
        public array $unionInsideArray,
        
        /** @var int|true */
        public int|bool $unionWithLiteralTrueType,
        
        /** @var int|false */
        public int|bool $unionWithLiteralFalseType,
        
        /** @var 404.42|1337.42 */
        public float $unionOfFloatValues,
        
        /** @var 42|1337 */
        public int $unionOfIntegerValues,
        
        /** @var 'foo'|'bar' */
        public string $unionOfStringValues,
    ) {}
}
```

## Class constants

```php
final readonly class SomeClassWithConstants
{
    public const FOO = 1337;
    
    public const BAR = 'bar';

    public const BAZ = 'baz';
}

final readonly class SomeClass
{
    public function __construct(
        /** @var SomeClassWithConstants::FOO|SomeClassWithConstants::BAR */
        public int|string $oneOfTwoCasesOfConstants,
        
        /** @param SomeClassWithConstants::BA* (matches `bar` or  `baz`) */
        public string $casesOfConstantsMatchingPattern,
    ) {}
}
```

## Enums

```php
enum SomeEnum
{
    case FOO;
    case BAR;
    case BAZ;
}

final readonly class SomeClass
{
    public function __construct(
        public SomeEnum $enum,

        /** @var SomeEnum::FOO|SomeEnum::BAR */
        public SomeEnum $oneOfTwoCasesOfEnum,

        /** @var SomeEnum::BA* (matches BAR or BAZ) */
        public SomeEnum $casesOfEnumMatchingPattern,
    ) {}
}
```

## key-of / value-of

The `key-of<T>` and `value-of<T>` types extract the key or value types from
enums, arrays, lists, and shaped arrays, including array constants. They are
compatible with the same syntax as accepted by [PHPStan] and [Psalm].

### key-of

```php
enum SomePureEnum
{
    case FOO;
    case BAR;
}

enum SomeBackedEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}

final readonly class SomeClassWithConstants
{
    public const SOME_ARRAY = ['foo' => 1, 'bar' => 2];
}

final readonly class SomeClass
{
    public function __construct(
        // Accepts 'FOO' or 'BAR' (the case names of the enum)
        /** @var key-of<SomePureEnum> */
        public string $pureEnumKey,

        // Also works with backed enums — still yields the case *names*
        /** @var key-of<SomeBackedEnum> */
        public string $backedEnumKey,

        // Accepts 'foo' or 'bar' (the keys of the shaped array)
        /** @var key-of<array{foo: string, bar: int}> */
        public string $shapedArrayKey,

        // Accepts the key type of the array (string here)
        /** @var key-of<array<string, int>> */
        public string $arrayKey,

        // Accepts the key type of the list (always int)
        /** @var key-of<list<string>> */
        public int $listKey,

        // Accepts 'foo' or 'bar' (the keys of the class constant array)
        /** @var key-of<SomeClassWithConstants::SOME_ARRAY> */
        public string $constantArrayKey,
    ) {}
}
```

### value-of

```php
enum SomeBackedStringEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}

final readonly class SomeClassWithConstants
{
    public const SOME_ARRAY = ['foo' => 1, 'bar' => 2];
}

final readonly class SomeClass
{
    public function __construct(
        // Accepts 'foo' or 'bar' (the backed values of the enum)
        /** @var value-of<SomeBackedStringEnum> */
        public string $enumValue,

        // Accepts string or int (the union of value types in the shaped array)
        /** @var value-of<array{foo: string, bar: int}> */
        public string|int $shapedArrayValue,

        // Accepts the value type of the array (SomeOtherClass here)
        /** @var value-of<array<string, SomeOtherClass>> */
        public SomeOtherClass $arrayValue,

        // Accepts the element type of the list (string here)
        /** @var value-of<list<string>> */
        public string $listValue,

        // Accepts 1 or 2 (the values of the class constant array)
        /** @var value-of<SomeClassWithConstants::SOME_ARRAY> */
        public int $constantArrayValue,
    ) {}
}
```
