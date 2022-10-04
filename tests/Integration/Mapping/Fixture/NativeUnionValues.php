<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

// @PHP8.0 move inside \CuyZ\Valinor\Tests\Integration\Mapping\UnionValuesMappingTest
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithConstants;

class NativeUnionValues
{
    public bool|float|int|string $scalarWithBoolean = 'Schwifty!';

    public bool|float|int|string $scalarWithFloat = 'Schwifty!';

    public bool|float|int|string $scalarWithInteger = 'Schwifty!';

    public bool|float|int|string $scalarWithString = 'Schwifty!';

    public string|null $nullableWithString = 'Schwifty!';

    public string|null $nullableWithNull = 'Schwifty!';

    /** @var int|true */
    public int|bool $intOrLiteralTrue = 42;

    /** @var int|false */
    public int|bool $intOrLiteralFalse = 42;

    /** @var ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A */
    public string|int $constantWithStringValue = 1653398288;

    /** @var ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A */
    public string|int $constantWithIntegerValue = 'some string value';
}

class NativeUnionValuesWithConstructor extends NativeUnionValues
{
    /**
     * @param int|true $intOrLiteralTrue
     * @param int|false $intOrLiteralFalse
     * @param ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A $constantWithStringValue
     * @param ObjectWithConstants::CONST_WITH_STRING_VALUE_A|ObjectWithConstants::CONST_WITH_INTEGER_VALUE_A $constantWithIntegerValue
     */
    public function __construct(
        bool|float|int|string $scalarWithBoolean = 'Schwifty!',
        bool|float|int|string $scalarWithFloat = 'Schwifty!',
        bool|float|int|string $scalarWithInteger = 'Schwifty!',
        bool|float|int|string $scalarWithString = 'Schwifty!',
        string|null $nullableWithString = 'Schwifty!',
        string|null $nullableWithNull = 'Schwifty!',
        int|bool $intOrLiteralTrue = 42,
        int|bool $intOrLiteralFalse = 42,
        string|int $constantWithStringValue = 1653398288,
        string|int $constantWithIntegerValue = 'some string value'
    ) {
        $this->scalarWithBoolean = $scalarWithBoolean;
        $this->scalarWithFloat = $scalarWithFloat;
        $this->scalarWithInteger = $scalarWithInteger;
        $this->scalarWithString = $scalarWithString;
        $this->nullableWithString = $nullableWithString;
        $this->nullableWithNull = $nullableWithNull;
        $this->intOrLiteralTrue = $intOrLiteralTrue;
        $this->intOrLiteralFalse = $intOrLiteralFalse;
        $this->constantWithStringValue = $constantWithStringValue;
        $this->constantWithIntegerValue = $constantWithIntegerValue;
    }
}
