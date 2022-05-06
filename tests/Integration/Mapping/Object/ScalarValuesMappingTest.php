<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use stdClass;
use stdClass as ObjectAlias;

final class ScalarValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'boolean' => true,
            'float' => 42.404,
            'integer' => 1337,
            'positiveInteger' => 1337,
            'negativeInteger' => -1337,
            'integerRangeWithPositiveValue' => 1337,
            'integerRangeWithNegativeValue' => -1337,
            'integerRangeWithMinAndMax' => 42,
            'integerValue' => 42,
            'string' => 'foo',
            'nonEmptyString' => 'bar',
            'stringValueWithSingleQuote' => 'baz',
            'stringValueWithDoubleQuote' => 'fiz',
            'classString' => self::class,
            'classStringOfDateTime' => DateTimeImmutable::class,
            'classStringOfAlias' => stdClass::class,
        ];

        foreach ([ScalarValues::class, ScalarValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(true, $result->boolean);
            self::assertSame(42.404, $result->float);
            self::assertSame(1337, $result->integer);
            self::assertSame(1337, $result->positiveInteger);
            self::assertSame(-1337, $result->negativeInteger);
            self::assertSame(1337, $result->integerRangeWithPositiveValue);
            self::assertSame(-1337, $result->integerRangeWithNegativeValue);
            self::assertSame(42, $result->integerRangeWithMinAndMax);
            self::assertSame(42, $result->integerValue); // @phpstan-ignore-line
            self::assertSame('foo', $result->string);
            self::assertSame('bar', $result->nonEmptyString);
            self::assertSame('baz', $result->stringValueWithSingleQuote); // @phpstan-ignore-line
            self::assertSame('fiz', $result->stringValueWithDoubleQuote); // @phpstan-ignore-line
            self::assertSame(self::class, $result->classString);
            self::assertSame(DateTimeImmutable::class, $result->classStringOfDateTime);
            self::assertSame(stdClass::class, $result->classStringOfAlias);
        }
    }

    public function test_value_that_cannot_be_casted_throws_exception(): void
    {
        try {
            $this->mapperBuilder->mapper()->map(SimpleObject::class, [
                'value' => new stdClass(),
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['value']->messages()[0];

            self::assertSame('1618736242', $error->code());
            self::assertSame('Cannot cast value of type `stdClass` to `string`.', (string)$error);
        }
    }

    public function test_empty_mandatory_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder->mapper()->map(SimpleObject::class, [
                'value' => null,
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['value']->messages()[0];

            self::assertSame('1618736242', $error->code());
            self::assertSame('Cannot be empty and must be filled with a value of type `string`.', (string)$error);
        }
    }
}

class ScalarValues
{
    public bool $boolean = false;

    public float $float = -1.0;

    public int $integer = -1;

    /** @var positive-int */
    public int $positiveInteger = 1;

    /** @var negative-int */
    public int $negativeInteger = -1;

    /** @var int<-1337, 1337> */
    public int $integerRangeWithPositiveValue = -1;

    /** @var int<-1337, 1337> */
    public int $integerRangeWithNegativeValue = -1;

    /** @var int<min, max> */
    public int $integerRangeWithMinAndMax = -1;

    /** @var 42 */
    public int $integerValue;

    public string $string = 'Schwifty!';

    /** @var non-empty-string */
    public string $nonEmptyString = 'Schwifty!';

    /** @var 'baz' */
    public string $stringValueWithSingleQuote;

    /** @var "fiz" */
    public string $stringValueWithDoubleQuote;

    /** @var class-string */
    public string $classString = stdClass::class;

    /** @var class-string<DateTimeInterface> */
    public string $classStringOfDateTime = DateTime::class;

    /** @var class-string<ObjectAlias> */
    public string $classStringOfAlias;
}

class ScalarValuesWithConstructor extends ScalarValues
{
    /**
     * @param positive-int $positiveInteger
     * @param negative-int $negativeInteger
     * @param int<-1337, 1337> $integerRangeWithPositiveValue
     * @param int<-1337, 1337> $integerRangeWithNegativeValue
     * @param int<min, max> $integerRangeWithMinAndMax
     * @param 42 $integerValue
     * @param non-empty-string $nonEmptyString
     * @param 'baz' $stringValueWithSingleQuote
     * @param "fiz" $stringValueWithDoubleQuote
     * @param class-string $classString
     * @param class-string<DateTimeInterface> $classStringOfDateTime
     * @param class-string<ObjectAlias> $classStringOfAlias
     */
    public function __construct(
        bool $boolean,
        float $float,
        int $integer,
        int $positiveInteger,
        int $negativeInteger,
        int $integerRangeWithPositiveValue,
        int $integerRangeWithNegativeValue,
        int $integerRangeWithMinAndMax,
        int $integerValue,
        string $string,
        string $nonEmptyString,
        string $stringValueWithSingleQuote,
        string $stringValueWithDoubleQuote,
        string $classString,
        string $classStringOfDateTime,
        string $classStringOfAlias
    ) {
        $this->boolean = $boolean;
        $this->float = $float;
        $this->integer = $integer;
        $this->positiveInteger = $positiveInteger;
        $this->negativeInteger = $negativeInteger;
        $this->integerRangeWithPositiveValue = $integerRangeWithPositiveValue;
        $this->integerRangeWithNegativeValue = $integerRangeWithNegativeValue;
        $this->integerRangeWithMinAndMax = $integerRangeWithMinAndMax;
        $this->integerValue = $integerValue;
        $this->string = $string;
        $this->nonEmptyString = $nonEmptyString;
        $this->stringValueWithSingleQuote = $stringValueWithSingleQuote;
        $this->stringValueWithDoubleQuote = $stringValueWithDoubleQuote;
        $this->classString = $classString;
        $this->classStringOfDateTime = $classStringOfDateTime;
        $this->classStringOfAlias = $classStringOfAlias;
    }
}
