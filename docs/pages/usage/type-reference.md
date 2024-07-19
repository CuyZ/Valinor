# Type reference

To prevent conflicts or duplication of the type annotations, this library tries
to handle most of the type annotations that are accepted by [PHPStan] and
[Psalm].

## Scalar

```php
final class SomeClass
{
    public function __construct(
        private bool $boolean,

        private float $float,

        private int $integer,

        /** @var positive-int */
        private int $positiveInteger,

        /** @var negative-int */
        private int $negativeInteger,

        /** @var non-positive-int */
        private int $nonPositiveInteger,

        /** @var non-negative-int */
        private int $nonNegativeInteger,

        /** @var int<-42, 1337> */
        private int $integerRange,

        /** @var int<min, 0> */
        private int $integerRangeWithMinRange,

        /** @var int<0, max> */
        private int $integerRangeWithMaxRange,

        private string $string,
        
        /** @var non-empty-string */
        private string $nonEmptyString,
        
        /** @var numeric-string */
        private string $numericString,

        /** @var class-string */
        private string $classString,

        /** @var class-string<SomeInterface> */
        private string $classStringOfAnInterface,
        
        /** @var value-of<SomeEnum> */
        private string $valueOfEnum,
    ) {}
}
```

## Object

```php
final class SomeClass
{
    public function __construct(
        private SomeClass $class,

        private DateTimeInterface $interface,

        /** @var SomeInterface&AnotherInterface */
        private object $intersection,

        /** @var SomeCollection<SomeClass> */
        private SomeCollection $classWithGeneric,
    ) {}
}

/**
 * @template T of object 
 */
final class SomeCollection
{
    public function __construct(
        /** @var array<T> */
        private array $objects,
    ) {}
}
```

## Array & lists

```php
final class SomeClass
{
    public function __construct(
        /** @var string[] */
        private array $simpleArray,

        /** @var array<string> */
        private array $arrayOfStrings,

        /** @var array<string, SomeClass> */
        private array $arrayOfClassWithStringKeys,

        /** @var array<int, SomeClass> */
        private array $arrayOfClassWithIntegerKeys,

        /** @var array<non-empty-string, string> */
        private array $arrayOfClassWithNonEmptyStringKeys,
        
        /** @var array<'foo'|'bar', string> */
        private array $arrayOfClassWithStringValueKeys,
        
        /** @var array<42|1337, string> */
        private array $arrayOfClassWithIntegerValueKeys,
        
        /** @var array<positive-int, string> */
        private array $arrayOfClassWithPositiveIntegerValueKeys,

        /** @var non-empty-array<string> */
        private array $nonEmptyArrayOfStrings,

        /** @var non-empty-array<string, SomeClass> */
        private array $nonEmptyArrayWithStringKeys,
        
        /** @var list<string> */
        private array $listOfStrings,
        
        /** @var non-empty-list<string> */
        private array $nonEmptyListOfStrings,

        /** @var array{foo: string, bar: int} */
        private array $shapedArray,

        /** @var array{foo: string, bar?: int} */
        private array $shapedArrayWithOptionalElement,

        /** @var array{string, bar: int} */
        private array $shapedArrayWithUndefinedKey,

        /** @var array{foo: string, ...} */
        private array $unsealedShapedArray,
        
        /** @var array{foo: string, ...array<string>} */
        private array $unsealedShapedArrayWithExplicitType,
        
        /** @var array{foo: string, ...array<int, string>} */
        private array $unsealedShapedArrayWithExplicitKeyAndType,
    ) {}
}
```

## Union

```php
final class SomeClass
{
    public function __construct(
        private int|string $simpleUnion,
        
        /** @var class-string<SomeInterface|AnotherInterface> */
        private string $unionOfClassString,
        
        /** @var array<SomeInterface|AnotherInterface> */
        private array $unionInsideArray,
        
        /** @var int|true */
        private int|bool $unionWithLiteralTrueType,
        
        /** @var int|false */
        private int|bool $unionWithLiteralFalseType,
        
        /** @var 404.42|1337.42 */
        private float $unionOfFloatValues,
        
        /** @var 42|1337 */
        private int $unionOfIntegerValues,
        
        /** @var 'foo'|'bar' */
        private string $unionOfStringValues,
    ) {}
}
```

## Class constants

```php
final class SomeClassWithConstants
{
    public const FOO = 1337;
    
    public const BAR = 'bar';

    public const BAZ = 'baz';
}

final class SomeClass
{
    public function __construct(
        /** @var SomeClassWithConstants::FOO|SomeClassWithConstants::BAR */
        private int|string $oneOfTwoCasesOfConstants,
        
        /** @param SomeClassWithConstants::BA* (matches `bar` or  `baz`) */
        private string $casesOfConstantsMatchingPattern,
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

final class SomeClass
{
    public function __construct(
        private SomeEnum $enum,

        /** @var SomeEnum::FOO|SomeEnum::BAR */
        private SomeEnum $oneOfTwoCasesOfEnum,

        /** @var SomeEnum::BA* (matches BAR or BAZ) */
        private SomeEnum $casesOfEnumMatchingPattern,
    ) {}
}
```
