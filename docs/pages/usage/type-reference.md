# Type reference

To prevent conflicts or duplication of the type annotations, this library tries
to handle most of the type annotations that are accepted by [PHPStan] and
[Psalm].

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
