<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithConstants;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ConstantValuesMappingTest extends IntegrationTestCase
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'anyConstantWithStringValue' => 'some string value',
            'anyConstantWithIntegerValue' => 1653398289,
            'constantStringValue' => 'another string value',
            'constantIntegerValue' => 1653398289,
            'constantFloatValue' => 404.512,
            'constantEnumValue' => BackedIntegerEnum::FOO,
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

        foreach ([ClassWithConstantValues::class, ClassWithConstantValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder()->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame('some string value', $result->anyConstantWithStringValue);
            self::assertSame(1653398289, $result->anyConstantWithIntegerValue);
            self::assertSame('another string value', $result->constantStringValue);
            self::assertSame(1653398289, $result->constantIntegerValue);
            self::assertSame(404.512, $result->constantFloatValue);
            self::assertSame(BackedIntegerEnum::FOO, $result->constantEnumValue);

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
            $this->mapperBuilder()
                ->mapper()
                ->map(ObjectWithConstants::class . '::CONST_WITH_STRING_*', 'some private string value');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[1607027306] Value 'some private string value' does not match any of 'some string value', 'another string value'.",
            ]);
        }
    }

    public function test_constant_not_matching_pattern_cannot_be_mapped(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map(ObjectWithConstants::class . '::CONST_WITH_STRING_*', 'some prefixed string value');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[1607027306] Value 'some prefixed string value' does not match any of 'some string value', 'another string value'.",
            ]);
        }
    }
}

class ClassWithConstantValues
{
    /** @var ObjectWithConstants::* */
    public mixed $anyConstantWithStringValue;

    /** @var ObjectWithConstants::* */
    public mixed $anyConstantWithIntegerValue;

    /** @var ObjectWithConstants::CONST_WITH_STRING_* */
    public string $constantStringValue;

    /** @var ObjectWithConstants::CONST_WITH_INTEGER_* */
    public int $constantIntegerValue;

    /** @var ObjectWithConstants::CONST_WITH_FLOAT_* */
    public float $constantFloatValue;

    /** @var ObjectWithConstants::CONST_WITH_ENUM_VALUE_* */
    public BackedIntegerEnum $constantEnumValue;

    /** @var ObjectWithConstants::CONST_WITH_ARRAY_VALUE_* */
    public array $constantArrayValue;

    /** @var ObjectWithConstants::CONST_WITH_NESTED_ARRAY_VALUE_* */
    public array $constantNestedArrayValue;
}

final class ClassWithConstantValuesWithConstructor extends ClassWithConstantValues
{
    /**
     * @param ObjectWithConstants::* $anyConstantWithStringValue
     * @param ObjectWithConstants::* $anyConstantWithIntegerValue
     * @param ObjectWithConstants::CONST_WITH_STRING_* $constantStringValue
     * @param ObjectWithConstants::CONST_WITH_INTEGER_* $constantIntegerValue
     * @param ObjectWithConstants::CONST_WITH_FLOAT_* $constantFloatValue
     * @param ObjectWithConstants::CONST_WITH_ENUM_VALUE_* $constantEnumValue
     * @param ObjectWithConstants::CONST_WITH_ARRAY_VALUE_* $constantArrayValue
     * @param ObjectWithConstants::CONST_WITH_NESTED_ARRAY_VALUE_* $constantNestedArrayValue
     */
    public function __construct(
        mixed $anyConstantWithStringValue,
        mixed $anyConstantWithIntegerValue,
        string $constantStringValue,
        int $constantIntegerValue,
        float $constantFloatValue,
        BackedIntegerEnum $constantEnumValue,
        array $constantArrayValue,
        array $constantNestedArrayValue
    ) {
        $this->anyConstantWithStringValue = $anyConstantWithStringValue;
        $this->anyConstantWithIntegerValue = $anyConstantWithIntegerValue;
        $this->constantStringValue = $constantStringValue;
        $this->constantIntegerValue = $constantIntegerValue;
        $this->constantFloatValue = $constantFloatValue;
        $this->constantEnumValue = $constantEnumValue;
        $this->constantArrayValue = $constantArrayValue;
        $this->constantNestedArrayValue = $constantNestedArrayValue;
    }
}
