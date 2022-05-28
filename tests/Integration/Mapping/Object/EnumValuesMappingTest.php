<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

/**
 * @requires PHP >= 8.1
 */
final class EnumValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'pureEnumWithFirstValue' => 'FOO',
            'pureEnumWithSecondValue' => 'BAR',
            'pureEnumWithPattern' => 'BAZ',
            'backedStringEnum' => 'foo',
            'backedStringEnumWithPattern' => 'baz',
            'backedIntegerEnum' => 404,
            'backedIntegerEnumWithPattern' => 1337,
        ];

        foreach ([EnumValues::class, EnumValuesWithConstructor::class] as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(PureEnum::FOO, $result->pureEnumWithFirstValue);
            self::assertSame(PureEnum::BAR, $result->pureEnumWithSecondValue);
            self::assertSame(PureEnum::BAZ, $result->pureEnumWithPattern);
            self::assertSame(BackedStringEnum::FOO, $result->backedStringEnum);
            self::assertSame(BackedStringEnum::BAZ, $result->backedStringEnumWithPattern);
            self::assertSame(BackedIntegerEnum::BAR, $result->backedIntegerEnum);
            self::assertSame(BackedIntegerEnum::BAZ, $result->backedIntegerEnumWithPattern);
        }
    }

    public function test_invalid_string_enum_value_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(BackedStringEnum::class, new StringableObject('fiz'));
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('Value object(' . StringableObject::class . ") does not match any of 'foo', 'bar', 'baz'.", (string)$error);
        }
    }

    public function test_invalid_integer_enum_value_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(BackedIntegerEnum::class, '512');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value '512' does not match any of 42, 404, 1337.", (string)$error);
        }
    }

    public function test_value_not_matching_pure_enum_case_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(PureEnum::class . '::FOO', 'fiz');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value 'fiz' does not match 'FOO'.", (string)$error);
        }
    }

    public function test_value_not_matching_backed_integer_enum_case_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(BackedIntegerEnum::class . '::FOO', '512');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value '512' does not match 42.", (string)$error);
        }
    }

    public function test_value_not_filled_for_pure_enum_case_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array{foo: ' . PureEnum::class . '::FOO}', []);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['foo']->messages()[0];

            self::assertSame('Cannot be empty and must be filled with a value matching type `FOO`.', (string)$error);
        }
    }

    public function test_value_not_filled_for_backed_integer_enum_case_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array{foo: ' . BackedIntegerEnum::class . '::FOO}', []);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['foo']->messages()[0];

            self::assertSame('Cannot be empty and must be filled with a value matching type `42`.', (string)$error);
        }
    }
}

class EnumValues
{
    public PureEnum $pureEnumWithFirstValue;

    public PureEnum $pureEnumWithSecondValue;

    /** @var PureEnum::BA* */
    public PureEnum $pureEnumWithPattern;

    public BackedStringEnum $backedStringEnum;

    /** @var BackedStringEnum::BA* */
    public BackedStringEnum $backedStringEnumWithPattern;

    public BackedIntegerEnum $backedIntegerEnum;

    /** @var BackedIntegerEnum::BA* */
    public BackedIntegerEnum $backedIntegerEnumWithPattern;
}

class EnumValuesWithConstructor extends EnumValues
{
    /**
     * @param PureEnum::BA* $pureEnumWithPattern
     * @param BackedStringEnum::BA* $backedStringEnumWithPattern
     * @param BackedIntegerEnum::BA* $backedIntegerEnumWithPattern
     */
    public function __construct(
        PureEnum $pureEnumWithFirstValue,
        PureEnum $pureEnumWithSecondValue,
        PureEnum $pureEnumWithPattern,
        BackedStringEnum $backedStringEnum,
        BackedStringEnum $backedStringEnumWithPattern,
        BackedIntegerEnum $backedIntegerEnum,
        BackedIntegerEnum $backedIntegerEnumWithPattern
    ) {
        $this->pureEnumWithFirstValue = $pureEnumWithFirstValue;
        $this->pureEnumWithSecondValue = $pureEnumWithSecondValue;
        $this->pureEnumWithPattern = $pureEnumWithPattern;
        $this->backedStringEnum = $backedStringEnum;
        $this->backedIntegerEnum = $backedIntegerEnum;
        $this->backedStringEnumWithPattern = $backedStringEnumWithPattern;
        $this->backedIntegerEnumWithPattern = $backedIntegerEnumWithPattern;
    }
}
