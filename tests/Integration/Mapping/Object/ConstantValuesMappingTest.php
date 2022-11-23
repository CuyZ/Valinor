<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithConstants;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithConstantsIncludingEnums;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class ConstantValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'constantStringValue' => 'another string value',
            'constantIntegerValue' => 1653398289,
            'constantFloatValue' => 404.512,
            'constantArrayValue' => [
                'string' => 'another string value',
                'integer' => 1653398289,
                'float' => 404.512,
            ],
            'constantNestedArrayValue' => [
                'another_nested_array' => [
                    'string' => 'another string value',
                    'integer' => 1653398289,
                    'float' => 404.512,
                ],
            ],
        ];

        // PHP8.1 remove condition
        if (PHP_VERSION_ID >= 8_01_00) {
            $source['constantEnumValue'] = BackedIntegerEnum::FOO;
        }

        // PHP8.1 merge classes
        $classes = PHP_VERSION_ID >= 8_01_00
            ? [ClassWithConstantValuesIncludingEnum::class, ClassWithConstantValuesIncludingEnumWithConstructor::class]
            : [ClassWithConstantValues::class, ClassWithConstantValuesWithConstructor::class];

        foreach ($classes as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame('another string value', $result->constantStringValue);
            self::assertSame(1653398289, $result->constantIntegerValue);
            self::assertSame(404.512, $result->constantFloatValue);

            // PHP8.1 remove condition
            if (PHP_VERSION_ID >= 8_01_00) {
                /** @var ClassWithConstantValuesIncludingEnum $result */
                self::assertSame(BackedIntegerEnum::FOO, $result->constantEnumValue);
            }

            self::assertSame([
                'string' => 'another string value',
                'integer' => 1653398289,
                'float' => 404.512,
            ], $result->constantArrayValue);
            self::assertSame([
                'another_nested_array' => [
                    'string' => 'another string value',
                    'integer' => 1653398289,
                    'float' => 404.512,
                ],
            ], $result->constantNestedArrayValue);
        }
    }

    public function test_private_constant_cannot_be_mapped(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(ObjectWithConstants::className() . '::CONST_WITH_STRING_*', 'some private string value');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame("Value 'some private string value' does not match any of 'some string value', 'another string value'.", (string)$error);
        }
    }

    public function test_constant_not_matching_pattern_cannot_be_mapped(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(ObjectWithConstants::className() . '::CONST_WITH_STRING_*', 'some prefixed string value');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame("Value 'some prefixed string value' does not match any of 'some string value', 'another string value'.", (string)$error);
        }
    }
}

class ClassWithConstantValues
{
    /** @var ObjectWithConstants::CONST_WITH_STRING_* */
    public string $constantStringValue;

    /** @var ObjectWithConstants::CONST_WITH_INTEGER_* */
    public int $constantIntegerValue;

    /** @var ObjectWithConstants::CONST_WITH_FLOAT_* */
    public float $constantFloatValue;

    /** @var ObjectWithConstants::CONST_WITH_ARRAY_VALUE_* */
    public array $constantArrayValue;

    /** @var ObjectWithConstants::CONST_WITH_NESTED_ARRAY_VALUE_* */
    public array $constantNestedArrayValue;
}

class ClassWithConstantValuesIncludingEnum extends ClassWithConstantValues
{
    /** @var ObjectWithConstantsIncludingEnums::CONST_WITH_ENUM_VALUE_* */
    public BackedIntegerEnum $constantEnumValue;
}

final class ClassWithConstantValuesWithConstructor extends ClassWithConstantValues
{
    /**
     * @param ObjectWithConstants::CONST_WITH_STRING_* $constantStringValue
     * @param ObjectWithConstants::CONST_WITH_INTEGER_* $constantIntegerValue
     * @param ObjectWithConstants::CONST_WITH_FLOAT_* $constantFloatValue
     * @param ObjectWithConstants::CONST_WITH_ARRAY_VALUE_* $constantArrayValue
     * @param ObjectWithConstants::CONST_WITH_NESTED_ARRAY_VALUE_* $constantNestedArrayValue
     */
    public function __construct(
        string $constantStringValue,
        int $constantIntegerValue,
        float $constantFloatValue,
        array $constantArrayValue,
        array $constantNestedArrayValue
    ) {
        $this->constantStringValue = $constantStringValue;
        $this->constantIntegerValue = $constantIntegerValue;
        $this->constantFloatValue = $constantFloatValue;
        $this->constantArrayValue = $constantArrayValue;
        $this->constantNestedArrayValue = $constantNestedArrayValue;
    }
}

final class ClassWithConstantValuesIncludingEnumWithConstructor extends ClassWithConstantValuesIncludingEnum
{
    /**
     * @param ObjectWithConstants::CONST_WITH_STRING_* $constantStringValue
     * @param ObjectWithConstants::CONST_WITH_INTEGER_* $constantIntegerValue
     * @param ObjectWithConstants::CONST_WITH_FLOAT_* $constantFloatValue
     * @param ObjectWithConstantsIncludingEnums::CONST_WITH_ENUM_VALUE_* $constantEnumValue
     * @param ObjectWithConstants::CONST_WITH_ARRAY_VALUE_* $constantArrayValue
     * @param ObjectWithConstants::CONST_WITH_NESTED_ARRAY_VALUE_* $constantNestedArrayValue
     */
    public function __construct(
        string $constantStringValue,
        int $constantIntegerValue,
        float $constantFloatValue,
        BackedIntegerEnum $constantEnumValue,
        array $constantArrayValue,
        array $constantNestedArrayValue
    ) {
        $this->constantStringValue = $constantStringValue;
        $this->constantIntegerValue = $constantIntegerValue;
        $this->constantFloatValue = $constantFloatValue;
        $this->constantEnumValue = $constantEnumValue;
        $this->constantArrayValue = $constantArrayValue;
        $this->constantNestedArrayValue = $constantNestedArrayValue;
    }
}
