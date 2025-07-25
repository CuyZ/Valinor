<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class EnumValuesMappingTest extends IntegrationTestCase
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'pureEnumWithFirstValue' => 'FOO',
            'pureEnumWithSecondValue' => 'BAR',
            'pureEnumWithFullNamespace' => 'FOO',
            'pureEnumWithPattern' => 'BAZ',
            'backedStringEnum' => 'foo',
            'backedStringEnumWithPattern' => 'baz',
            'backedIntegerEnum' => 404,
            'backedIntegerEnumWithPattern' => 1337,
        ];

        foreach ([EnumValues::class, EnumValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder()->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(PureEnum::FOO, $result->pureEnumWithFirstValue);
            self::assertSame(PureEnum::BAR, $result->pureEnumWithSecondValue);
            self::assertSame(PureEnum::FOO, $result->pureEnumWithFullNamespace);
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
            $this->mapperBuilder()->mapper()->map(BackedStringEnum::class, new StringableObject('fiz'));
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[cannot_resolve_type_from_union] Value object(' . StringableObject::class . ") does not match any of 'foo', 'bar', 'baz'.",
            ]);
        }
    }

    public function test_invalid_integer_enum_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(BackedIntegerEnum::class, '512');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[cannot_resolve_type_from_union] Value '512' does not match any of 42, 404, 1337.",
            ]);
        }
    }

    public function test_value_not_matching_pure_enum_case_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(PureEnum::class . '::FOO', 'fiz');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[invalid_string_value] Value 'fiz' does not match string value 'FOO'.",
            ]);
        }
    }

    public function test_value_not_matching_backed_integer_enum_case_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(BackedIntegerEnum::class . '::FOO', '512');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[invalid_integer_value] Value '512' does not match integer value 42.",
            ]);
        }
    }

    public function test_value_not_filled_for_pure_enum_case_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array{foo: ' . PureEnum::class . '::FOO}', []);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'foo' => '[missing_value] Cannot be empty and must be filled with a value matching type `FOO`.',
            ]);
        }
    }

    public function test_value_not_filled_for_backed_integer_enum_case_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array{foo: ' . BackedIntegerEnum::class . '::FOO}', []);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'foo' => '[missing_value] Cannot be empty and must be filled with a value matching type `42`.',
            ]);
        }
    }

    public function test_nested_error_path_is_correctly_flattened_when_using_single_argument_for_enum(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(BackedIntegerEnum::class, 'foo');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[cannot_resolve_type_from_union] Value 'foo' does not match any of 42, 404, 1337.",
            ]);
        }
    }

    public function test_error_path_is_correct_when_using_nested_argument_for_enum(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(BackedIntegerEnum::class, ['value' => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'value' => "[cannot_resolve_type_from_union] Value 'foo' does not match any of 42, 404, 1337.",
            ]);
        }
    }
}

class EnumValues
{
    public PureEnum $pureEnumWithFirstValue;

    public PureEnum $pureEnumWithSecondValue;

    /** @var \CuyZ\Valinor\Tests\Fixture\Enum\PureEnum */
    public mixed $pureEnumWithFullNamespace;

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
     * @param \CuyZ\Valinor\Tests\Fixture\Enum\PureEnum $pureEnumWithFullNamespace
     * @param PureEnum::BA* $pureEnumWithPattern
     * @param BackedStringEnum::BA* $backedStringEnumWithPattern
     * @param BackedIntegerEnum::BA* $backedIntegerEnumWithPattern
     */
    public function __construct(
        PureEnum $pureEnumWithFirstValue,
        PureEnum $pureEnumWithSecondValue,
        mixed $pureEnumWithFullNamespace,
        PureEnum $pureEnumWithPattern,
        BackedStringEnum $backedStringEnum,
        BackedStringEnum $backedStringEnumWithPattern,
        BackedIntegerEnum $backedIntegerEnum,
        BackedIntegerEnum $backedIntegerEnumWithPattern
    ) {
        $this->pureEnumWithFirstValue = $pureEnumWithFirstValue;
        $this->pureEnumWithSecondValue = $pureEnumWithSecondValue;
        $this->pureEnumWithFullNamespace = $pureEnumWithFullNamespace;
        $this->pureEnumWithPattern = $pureEnumWithPattern;
        $this->backedStringEnum = $backedStringEnum;
        $this->backedIntegerEnum = $backedIntegerEnum;
        $this->backedStringEnumWithPattern = $backedStringEnumWithPattern;
        $this->backedIntegerEnumWithPattern = $backedIntegerEnumWithPattern;
    }
}
