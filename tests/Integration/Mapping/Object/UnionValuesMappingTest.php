<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\NativeUnionValues;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\NativeUnionValuesWithConstructor;

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
            'integerValue' => 1337,
            'stringValueWithSingleQuote' => 'bar',
            'stringValueWithDoubleQuote' => 'fiz',
        ];

        $classes = [UnionValues::class, UnionValuesWithConstructor::class];

        if (PHP_VERSION_ID >= 8_00_00) {
            $classes[] = NativeUnionValues::class;
            $classes[] = NativeUnionValuesWithConstructor::class;
        }

        foreach ($classes as $class) {
            try {
                $result = $this->mapperBuilder->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(true, $result->scalarWithBoolean);
            self::assertSame(42.404, $result->scalarWithFloat);
            self::assertSame(1337, $result->scalarWithInteger);
            self::assertSame('foo', $result->scalarWithString);
            self::assertSame('bar', $result->nullableWithString);
            self::assertSame(null, $result->nullableWithNull);

            if ($result instanceof UnionValues) {
                self::assertSame(1337, $result->integerValue);
                self::assertSame('bar', $result->stringValueWithSingleQuote);
                self::assertSame('fiz', $result->stringValueWithDoubleQuote);
            }
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

    /** @var 42|1337 */
    public int $integerValue = 42;

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
     * @param 42|1337 $integerValue
     * @param 'foo'|'bar' $stringValueWithSingleQuote
     * @param "baz"|"fiz" $stringValueWithDoubleQuote
     */
    public function __construct(
        $scalarWithBoolean = 'Schwifty!',
        $scalarWithFloat = 'Schwifty!',
        $scalarWithInteger = 'Schwifty!',
        $scalarWithString = 'Schwifty!',
        $nullableWithString = 'Schwifty!',
        $nullableWithNull = 'Schwifty!',
        int $integerValue = 42,
        string $stringValueWithSingleQuote = 'foo',
        string $stringValueWithDoubleQuote = 'baz'
    ) {
        $this->scalarWithBoolean = $scalarWithBoolean;
        $this->scalarWithFloat = $scalarWithFloat;
        $this->scalarWithInteger = $scalarWithInteger;
        $this->scalarWithString = $scalarWithString;
        $this->nullableWithString = $nullableWithString;
        $this->nullableWithNull = $nullableWithNull;
        $this->integerValue = $integerValue;
        $this->stringValueWithSingleQuote = $stringValueWithSingleQuote;
        $this->stringValueWithDoubleQuote = $stringValueWithDoubleQuote;
    }
}
