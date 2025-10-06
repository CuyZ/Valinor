<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use stdClass;
use stdClass as ObjectAlias;

final class ScalarValuesMappingTest extends IntegrationTestCase
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'scalarWithInteger' => 1337,
            'scalarWithFloat' => 42.404,
            'scalarWithString' => 'foo',
            'scalarWithBoolean' => true,
            'boolean' => true,
            'float' => 42.404,
            'floatWithInteger' => 42,
            'positiveFloatValue' => 42.404,
            'negativeFloatValue' => -42.404,
            'integer' => 1337,
            'positiveInteger' => 1337,
            'negativeInteger' => -1337,
            'nonPositiveInteger' => -1337,
            'nonNegativeInteger' => 1337,
            'integerRangeWithPositiveValue' => 1337,
            'integerRangeWithNegativeValue' => -1337,
            'integerRangeWithMinAndMax' => 42,
            'positiveIntegerValue' => 42,
            'negativeIntegerValue' => -42,
            'string' => 'foo',
            'nonEmptyString' => 'bar',
            'numericString' => '1337',
            'stringValueWithSingleQuote' => 'baz',
            'stringValueContainingSpaceWithSingleQuote' => 'baz baz',
            'stringValueContainingSpecialCharsWithSingleQuote' => 'baz & $ § % baz',
            'stringValueWithDoubleQuote' => 'fiz',
            'stringValueContainingSpaceWithDoubleQuote' => 'fiz fiz',
            'stringValueContainingSpecialCharsWithDoubleQuote' => 'fiz & $ § % fiz',
            'classString' => self::class,
            'classStringOfDateTime' => DateTimeImmutable::class,
            'classStringOfAlias' => stdClass::class,
            'arrayKeyWithString' => 'foo',
            'arrayKeyWithInteger' => 42,
        ];

        foreach ([ScalarValues::class, ScalarValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder()->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(1337, $result->scalarWithInteger);
            self::assertSame(42.404, $result->scalarWithFloat);
            self::assertSame('foo', $result->scalarWithString);
            self::assertSame(true, $result->scalarWithBoolean);
            self::assertSame(true, $result->boolean);
            self::assertSame(42.404, $result->float);
            self::assertSame(42.0, $result->floatWithInteger);
            self::assertSame(42.404, $result->positiveFloatValue); // @phpstan-ignore-line
            self::assertSame(-42.404, $result->negativeFloatValue); // @phpstan-ignore-line
            self::assertSame(1337, $result->integer);
            self::assertSame(1337, $result->positiveInteger);
            self::assertSame(-1337, $result->negativeInteger);
            self::assertSame(-1337, $result->nonPositiveInteger);
            self::assertSame(1337, $result->nonNegativeInteger);
            self::assertSame(1337, $result->integerRangeWithPositiveValue);
            self::assertSame(-1337, $result->integerRangeWithNegativeValue);
            self::assertSame(42, $result->integerRangeWithMinAndMax);
            self::assertSame(42, $result->positiveIntegerValue); // @phpstan-ignore-line
            self::assertSame(-42, $result->negativeIntegerValue); // @phpstan-ignore-line
            self::assertSame('foo', $result->string);
            self::assertSame('bar', $result->nonEmptyString);
            self::assertSame('1337', $result->numericString);
            self::assertSame('baz', $result->stringValueWithSingleQuote); // @phpstan-ignore-line
            self::assertSame('baz baz', $result->stringValueContainingSpaceWithSingleQuote); // @phpstan-ignore-line
            self::assertSame('baz & $ § % baz', $result->stringValueContainingSpecialCharsWithSingleQuote); // @phpstan-ignore-line
            self::assertSame('fiz', $result->stringValueWithDoubleQuote); // @phpstan-ignore-line
            self::assertSame('fiz fiz', $result->stringValueContainingSpaceWithDoubleQuote); // @phpstan-ignore-line
            self::assertSame('fiz & $ § % fiz', $result->stringValueContainingSpecialCharsWithDoubleQuote); // @phpstan-ignore-line
            self::assertSame(self::class, $result->classString);
            self::assertSame(DateTimeImmutable::class, $result->classStringOfDateTime);
            self::assertSame(stdClass::class, $result->classStringOfAlias);
            self::assertSame('foo', $result->arrayKeyWithString);
            self::assertSame(42, $result->arrayKeyWithInteger);
        }
    }

    public function test_can_map_to_float_with_integer_value(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map('float', 1337);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(1337.0, $result);
    }

    public function test_value_with_invalid_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(SimpleObject::class, new stdClass());
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[invalid_string] Value object(stdClass) is not a valid string.',
            ]);
        }
    }

    public function test_invalid_array_key_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array-key', new stdClass());
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[invalid_array_key] Value object(stdClass) is not a valid array key.',
            ]);
        }
    }
}

class ScalarValues
{
    /** @var scalar */
    public mixed $scalarWithInteger = -1;

    /** @var scalar */
    public mixed $scalarWithFloat = -1;

    /** @var scalar */
    public mixed $scalarWithString = -1;

    /** @var scalar */
    public mixed $scalarWithBoolean = -1;

    public bool $boolean = false;

    public float $float = -1.0;

    public float $floatWithInteger = -1.0;

    /** @var 42.404 */
    public float $positiveFloatValue;

    /** @var -42.404 */
    public float $negativeFloatValue;

    public int $integer = -1;

    /** @var positive-int */
    public int $positiveInteger = 1;

    /** @var negative-int */
    public int $negativeInteger = -1;

    /** @var non-positive-int */
    public int $nonPositiveInteger = -1;

    /** @var non-negative-int */
    public int $nonNegativeInteger = 1;

    /** @var int<-1337, 1337> */
    public int $integerRangeWithPositiveValue = -1;

    /** @var int<-1337, 1337> */
    public int $integerRangeWithNegativeValue = -1;

    /** @var int<min, max> */
    public int $integerRangeWithMinAndMax = -1;

    /** @var 42 */
    public int $positiveIntegerValue;

    /** @var -42 */
    public int $negativeIntegerValue;

    public string $string = 'Schwifty!';

    /** @var non-empty-string */
    public string $nonEmptyString = 'Schwifty!';

    /** @var numeric-string */
    public string $numericString = '42';

    /** @var 'baz' */
    public string $stringValueWithSingleQuote;

    /** @var 'baz baz' */
    public string $stringValueContainingSpaceWithSingleQuote;

    /** @var 'baz & $ § % baz' */
    public string $stringValueContainingSpecialCharsWithSingleQuote;

    /** @var "fiz" */
    public string $stringValueWithDoubleQuote;

    /** @var "fiz fiz" */
    public string $stringValueContainingSpaceWithDoubleQuote;

    /** @var "fiz & $ § % fiz" */
    public string $stringValueContainingSpecialCharsWithDoubleQuote;

    /** @var class-string */
    public string $classString = stdClass::class;

    /** @var class-string<DateTimeInterface> */
    public string $classStringOfDateTime = DateTime::class;

    /** @var class-string<ObjectAlias> */
    public string $classStringOfAlias;

    /** @var array-key */
    public string|int $arrayKeyWithString;

    /** @var array-key */
    public string|int $arrayKeyWithInteger;
}

class ScalarValuesWithConstructor extends ScalarValues
{
    /**
     * @param scalar $scalarWithInteger
     * @param scalar $scalarWithFloat
     * @param scalar $scalarWithString
     * @param scalar $scalarWithBoolean
     * @param 42.404 $positiveFloatValue
     * @param -42.404 $negativeFloatValue
     * @param positive-int $positiveInteger
     * @param negative-int $negativeInteger
     * @param non-positive-int $nonPositiveInteger
     * @param non-negative-int $nonNegativeInteger
     * @param int<-1337, 1337> $integerRangeWithPositiveValue
     * @param int<-1337, 1337> $integerRangeWithNegativeValue
     * @param int<min, max> $integerRangeWithMinAndMax
     * @param 42 $positiveIntegerValue
     * @param -42 $negativeIntegerValue
     * @param non-empty-string $nonEmptyString
     * @param numeric-string $numericString
     * @param 'baz' $stringValueWithSingleQuote
     * @param 'baz baz' $stringValueContainingSpaceWithSingleQuote
     * @param 'baz & $ § % baz' $stringValueContainingSpecialCharsWithSingleQuote
     * @param "fiz" $stringValueWithDoubleQuote
     * @param "fiz fiz" $stringValueContainingSpaceWithDoubleQuote
     * @param "fiz & $ § % fiz" $stringValueContainingSpecialCharsWithDoubleQuote
     * @param class-string $classString
     * @param class-string<DateTimeInterface> $classStringOfDateTime
     * @param class-string<ObjectAlias> $classStringOfAlias
     * @param array-key $arrayKeyWithString
     * @param array-key $arrayKeyWithInteger
     */
    public function __construct(
        mixed $scalarWithInteger,
        mixed $scalarWithFloat,
        mixed $scalarWithString,
        mixed $scalarWithBoolean,
        bool $boolean,
        float $float,
        float $floatWithInteger,
        float $positiveFloatValue,
        float $negativeFloatValue,
        int $integer,
        int $positiveInteger,
        int $negativeInteger,
        int $nonPositiveInteger,
        int $nonNegativeInteger,
        int $integerRangeWithPositiveValue,
        int $integerRangeWithNegativeValue,
        int $integerRangeWithMinAndMax,
        int $positiveIntegerValue,
        int $negativeIntegerValue,
        string $string,
        string $nonEmptyString,
        string $numericString,
        string $stringValueWithSingleQuote,
        string $stringValueContainingSpaceWithSingleQuote,
        string $stringValueContainingSpecialCharsWithSingleQuote,
        string $stringValueWithDoubleQuote,
        string $stringValueContainingSpaceWithDoubleQuote,
        string $stringValueContainingSpecialCharsWithDoubleQuote,
        string $classString,
        string $classStringOfDateTime,
        string $classStringOfAlias,
        int|string $arrayKeyWithString,
        int|string $arrayKeyWithInteger,
    ) {
        $this->scalarWithInteger = $scalarWithInteger;
        $this->scalarWithFloat = $scalarWithFloat;
        $this->scalarWithString = $scalarWithString;
        $this->scalarWithBoolean = $scalarWithBoolean;
        $this->boolean = $boolean;
        $this->float = $float;
        $this->floatWithInteger = $floatWithInteger;
        $this->positiveFloatValue = $positiveFloatValue;
        $this->negativeFloatValue = $negativeFloatValue;
        $this->integer = $integer;
        $this->positiveInteger = $positiveInteger;
        $this->negativeInteger = $negativeInteger;
        $this->nonPositiveInteger = $nonPositiveInteger;
        $this->nonNegativeInteger = $nonNegativeInteger;
        $this->integerRangeWithPositiveValue = $integerRangeWithPositiveValue;
        $this->integerRangeWithNegativeValue = $integerRangeWithNegativeValue;
        $this->integerRangeWithMinAndMax = $integerRangeWithMinAndMax;
        $this->positiveIntegerValue = $positiveIntegerValue;
        $this->negativeIntegerValue = $negativeIntegerValue;
        $this->string = $string;
        $this->nonEmptyString = $nonEmptyString;
        $this->numericString = $numericString;
        $this->stringValueWithSingleQuote = $stringValueWithSingleQuote;
        $this->stringValueContainingSpaceWithSingleQuote = $stringValueContainingSpaceWithSingleQuote;
        $this->stringValueContainingSpecialCharsWithSingleQuote = $stringValueContainingSpecialCharsWithSingleQuote;
        $this->stringValueWithDoubleQuote = $stringValueWithDoubleQuote;
        $this->stringValueContainingSpaceWithDoubleQuote = $stringValueContainingSpaceWithDoubleQuote;
        $this->stringValueContainingSpecialCharsWithDoubleQuote = $stringValueContainingSpecialCharsWithDoubleQuote;
        $this->classString = $classString;
        $this->classStringOfDateTime = $classStringOfDateTime;
        $this->classStringOfAlias = $classStringOfAlias;
        $this->arrayKeyWithString = $arrayKeyWithString;
        $this->arrayKeyWithInteger = $arrayKeyWithInteger;
    }
}
