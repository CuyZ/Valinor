<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithConstants;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\NativeUnionValues;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\NativeUnionValuesWithConstructor;
use DateTimeImmutable;
use DateTimeInterface;

final class UnionValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'scalarWithBoolean' => true,
            'scalarWithFloat' => 42.404,
            'scalarWithInteger' => 1337,
            'scalarWithString' => 'foo',
            'nullableWithString' => 'bar',
            'nullableWithNull' => null,
            'intOrLiteralTrue' => true,
            'intOrLiteralFalse' => false,
            'dateTimeOrNull' => 1667754013,
            'nullOrDateTime' => 1667754014,
            'constantWithStringValue' => 'some string value',
            'constantWithIntegerValue' => 1653398288,
        ];

        $classes = [UnionValues::class, UnionValuesWithConstructor::class];

        if (PHP_VERSION_ID >= 8_00_00) {
            $classes[] = NativeUnionValues::class;
            $classes[] = NativeUnionValuesWithConstructor::class;
        }

        foreach ($classes as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(true, $result->scalarWithBoolean);
            self::assertSame(42.404, $result->scalarWithFloat);
            self::assertSame(1337, $result->scalarWithInteger);
            self::assertSame('foo', $result->scalarWithString);
            self::assertSame('bar', $result->nullableWithString);
            self::assertSame(null, $result->nullableWithNull);
            self::assertSame(true, $result->intOrLiteralTrue);
            self::assertSame(false, $result->intOrLiteralFalse);
            self::assertInstanceOf(DateTimeInterface::class, $result->dateTimeOrNull);
            self::assertInstanceOf(DateTimeInterface::class, $result->nullOrDateTime);
            self::assertSame((new DateTimeImmutable('@1667754013'))->format('U'), $result->dateTimeOrNull->format('U'));
            self::assertSame((new DateTimeImmutable('@1667754014'))->format('U'), $result->nullOrDateTime->format('U'));
            self::assertSame('some string value', $result->constantWithStringValue);
            self::assertSame(1653398288, $result->constantWithIntegerValue);
        }

        $source = [
            'positiveFloatValue' => 1337.42,
            'negativeFloatValue' => -1337.42,
            'positiveIntegerValue' => 1337,
            'negativeIntegerValue' => -1337,
            'stringValueWithSingleQuote' => 'bar',
            'stringValueWithDoubleQuote' => 'fiz',
        ];

        foreach ([UnionOfFixedValues::class, UnionOfFixedValuesWithConstructor::class] as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(1337.42, $result->positiveFloatValue);
            self::assertSame(-1337.42, $result->negativeFloatValue);
            self::assertSame(1337, $result->positiveIntegerValue);
            self::assertSame(-1337, $result->negativeIntegerValue);
            self::assertSame('bar', $result->stringValueWithSingleQuote);
            self::assertSame('fiz', $result->stringValueWithDoubleQuote);
        }
    }

    public function test_filled_source_value_is_casted_when_union_contains_three_types_including_null(): void
    {
        try {
            $result = (new MapperBuilder())
                ->flexible()
                ->mapper()
                ->map('null|int|string', new StringableObject('foo'));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_enum_in_union_type_is_casted_properly(): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map('int|' . PureEnum::class, 'FOO');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(PureEnum::FOO, $result);
    }

    public function test_source_value_is_casted_when_other_type_cannot_be_caster(): void
    {
        try {
            $result = (new MapperBuilder())
                ->flexible()
                ->mapper()
                ->map('string[]|string', new StringableObject('foo'));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result);
    }

    public function test_invalid_value_is_not_casted_when_casting_mode_is_disabled(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('string|float', 42);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame('Value 42 does not match any of `string`, `float`.', (string)$error);
        }
    }
}

class UnionValues
{
    /** @var bool|float|int|string */
    public $scalarWithBoolean = 'Schwifty!';

    /** @var bool|float|int|string */
    public $scalarWithFloat = 'Schwifty!';

    /** @var bool|float|int|string */
    public $scalarWithInteger = 'Schwifty!';

    /** @var bool|float|int|string */
    public $scalarWithString = 'Schwifty!';

    /** @var string|null|float */
    public $nullableWithString = 'Schwifty!';

    /** @var string|null|float */
    public $nullableWithNull = 'Schwifty!';

    /** @var int|true */
    public $intOrLiteralTrue = 42;

    /** @var int|false */
    public $intOrLiteralFalse = 42;

    /** @var DateTimeInterface|null */
    public $dateTimeOrNull;

    /** @var null|DateTimeInterface */
    public $nullOrDateTime;

    /** @var ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A */
    public $constantWithStringValue = 1653398288;

    /** @var ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A */
    public $constantWithIntegerValue = 'some string value';
}

class UnionOfFixedValues
{
    /** @var 404.42|1337.42 */
    public float $positiveFloatValue = 404.42;

    /** @var -404.42|-1337.42 */
    public float $negativeFloatValue = -404.42;

    /** @var 42|1337 */
    public int $positiveIntegerValue = 42;

    /** @var -42|-1337 */
    public int $negativeIntegerValue = -42;

    /** @var 'foo'|'bar' */
    public string $stringValueWithSingleQuote;

    /** @var "baz"|"fiz" */
    public string $stringValueWithDoubleQuote;
}

class UnionValuesWithConstructor extends UnionValues
{
    /**
     * @param bool|float|int|string $scalarWithBoolean
     * @param bool|float|int|string $scalarWithFloat
     * @param bool|float|int|string $scalarWithInteger
     * @param bool|float|int|string $scalarWithString
     * @param string|null|float $nullableWithString
     * @param string|null|float $nullableWithNull
     * @param int|true $intOrLiteralTrue
     * @param int|false $intOrLiteralFalse
     * @param DateTimeInterface|null $dateTimeOrNull
     * @param null|DateTimeInterface $nullOrDateTime
     * @param ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A $constantWithStringValue
     * @param ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A $constantWithIntegerValue
     */
    public function __construct(
        $scalarWithBoolean = 'Schwifty!',
        $scalarWithFloat = 'Schwifty!',
        $scalarWithInteger = 'Schwifty!',
        $scalarWithString = 'Schwifty!',
        $nullableWithString = 'Schwifty!',
        $nullableWithNull = 'Schwifty!',
        $intOrLiteralTrue = 42,
        $intOrLiteralFalse = 42,
        $dateTimeOrNull = null,
        $nullOrDateTime = null,
        $constantWithStringValue = 1653398288,
        $constantWithIntegerValue = 'some string value'
    ) {
        $this->scalarWithBoolean = $scalarWithBoolean;
        $this->scalarWithFloat = $scalarWithFloat;
        $this->scalarWithInteger = $scalarWithInteger;
        $this->scalarWithString = $scalarWithString;
        $this->nullableWithString = $nullableWithString;
        $this->nullableWithNull = $nullableWithNull;
        $this->intOrLiteralTrue = $intOrLiteralTrue;
        $this->intOrLiteralFalse = $intOrLiteralFalse;
        $this->dateTimeOrNull = $dateTimeOrNull;
        $this->nullOrDateTime = $nullOrDateTime;
        $this->constantWithStringValue = $constantWithStringValue;
        $this->constantWithIntegerValue = $constantWithIntegerValue;
    }
}

class UnionOfFixedValuesWithConstructor extends UnionOfFixedValues
{
    /**
     * @param 404.42|1337.42 $positiveFloatValue
     * @param -404.42|-1337.42 $negativeFloatValue
     * @param 42|1337 $positiveIntegerValue
     * @param -42|-1337 $negativeIntegerValue
     * @param 'foo'|'bar' $stringValueWithSingleQuote
     * @param "baz"|"fiz" $stringValueWithDoubleQuote
     */
    public function __construct(
        float $positiveFloatValue = 404.42,
        float $negativeFloatValue = -404.42,
        int $positiveIntegerValue = 42,
        int $negativeIntegerValue = -42,
        string $stringValueWithSingleQuote = 'foo',
        string $stringValueWithDoubleQuote = 'baz'
    ) {
        $this->positiveFloatValue = $positiveFloatValue;
        $this->negativeFloatValue = $negativeFloatValue;
        $this->positiveIntegerValue = $positiveIntegerValue;
        $this->negativeIntegerValue = $negativeIntegerValue;
        $this->stringValueWithSingleQuote = $stringValueWithSingleQuote;
        $this->stringValueWithDoubleQuote = $stringValueWithDoubleQuote;
    }
}
