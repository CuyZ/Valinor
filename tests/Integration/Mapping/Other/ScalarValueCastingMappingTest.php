<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;

use function strtoupper;

final class ScalarValueCastingMappingTest extends IntegrationTestCase
{
    #[TestWith(['type' => 'int', 'value' => '000', 'expected' => 0])]
    #[TestWith(['type' => 'int', 'value' => '040', 'expected' => 40])]
    #[TestWith(['type' => 'int', 'value' => '00040', 'expected' => 40])]
    #[TestWith(['type' => 'float', 'value' => '0001337.404', 'expected' => 1337.404])]
    #[TestWith(['type' => 'int<1, 500>', 'value' => '060', 'expected' => 60])]
    #[TestWith(['type' => 'int<1, 500>', 'value' => '042', 'expected' => 42])]
    #[TestWith(['type' => 'int<1, 500>', 'value' => '000404', 'expected' => 404])]
    #[TestWith(['type' => '0|40|404', 'value' => '000', 'expected' => 0])]
    #[TestWith(['type' => '0|40|404', 'value' => '040', 'expected' => 40])]
    #[TestWith(['type' => '0|40|404', 'value' => '000404', 'expected' => 404])]
    #[TestWith(['type' => 'positive-int', 'value' => '040', 'expected' => 40])]
    #[TestWith(['type' => 'positive-int', 'value' => '000404', 'expected' => 404])]
    #[TestWith(['type' => 'non-negative-int', 'value' => '000', 'expected' => 0])]
    #[TestWith(['type' => 'non-negative-int', 'value' => '040', 'expected' => 40])]
    #[TestWith(['type' => 'non-negative-int', 'value' => '000404', 'expected' => 404])]
    #[TestWith(['type' => 'string', 'value' => '42', 'expected' => '42'])]
    #[TestWith(['type' => 'string', 'value' => '1337.404', 'expected' => '1337.404'])]
    #[TestWith(['type' => 'bool', 'value' => 'true', 'expected' => true])]
    #[TestWith(['type' => 'bool', 'value' => '0', 'expected' => false])]
    #[TestWith(['type' => 'array{string, foo: int, bar?: float}', 'value' => ['hello', 'foo' => '42'], 'expected' => ['hello', 'foo' => 42]])]
    #[TestWith(['type' => BackedStringEnum::class, 'value' => new StringableObject('foo'), 'expected' => BackedStringEnum::FOO])]
    #[TestWith(['type' => BackedIntegerEnum::class, 'value' => '42', 'expected' => BackedIntegerEnum::FOO])]
    #[TestWith(['type' => 'null|int|string', 'value' => new StringableObject('foo'), 'expected' => 'foo'])]
    #[TestWith(['type' => 'string[]|string', 'value' => new StringableObject('foo'), 'expected' => 'foo'])]
    #[TestWith(['type' => 'array-key', 'value' => new StringableObject('foo'), 'expected' => 'foo'])]
    public function test_scalar_values_are_casted_properly(string $type, mixed $value, mixed $expected): void
    {
        try {
            $result = $this
                ->mapperBuilder()
                ->allowScalarValueCasting()
                ->mapper()
                ->map($type, $value);

            self::assertSame($expected, $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    /** @param non-empty-string $error */
    #[TestWith(['type' => 'int', 'value' => true, 'error' => '[invalid_integer] Value true is not a valid integer.'])]
    #[TestWith(['type' => 'int', 'value' => false, 'error' => '[invalid_integer] Value false is not a valid integer.'])]
    #[TestWith(['type' => 'float', 'value' => true, 'error' => '[invalid_float] Value true is not a valid float.'])]
    #[TestWith(['type' => 'float', 'value' => false, 'error' => '[invalid_float] Value false is not a valid float.'])]
    public function test_boolean_value_is_not_cast_to_int_or_float(string $type, bool $value, string $error): void
    {
        try {
            $this
                ->mapperBuilder()
                ->allowScalarValueCasting()
                ->mapper()
                ->map($type, $value);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, ['*root*' => $error]);
        }
    }

    #[DataProvider('integer_values_are_casted_properly_data_provider')]
    public function test_integer_values_are_casted_properly(string $type, mixed $value, mixed $expected): void
    {
        try {
            $result = $this
                ->mapperBuilder()
                ->allowScalarValueCasting()
                ->mapper()
                ->map($type, $value);

            self::assertSame($expected, $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public static function integer_values_are_casted_properly_data_provider(): iterable
    {
        yield 'int with very large integer from string' => [
            'type' => 'int',
            'value' => (string)(PHP_INT_MAX - 1),
            'expected' => PHP_INT_MAX - 1,
        ];

        yield 'non negative int with very large integer from string' => [
            'type' => 'non-negative-int',
            'value' => (string)(PHP_INT_MAX - 1),
            'expected' => PHP_INT_MAX - 1,
        ];

        yield 'positive int with very large integer from string' => [
            'type' => 'positive-int',
            'value' => (string)(PHP_INT_MAX - 1),
            'expected' => PHP_INT_MAX - 1,
        ];

        yield 'integer range with very large integer from string' => [
            'type' => 'int<0, max>',
            'value' => (string)(PHP_INT_MAX - 1),
            'expected' => PHP_INT_MAX - 1,
        ];

        yield 'integer value with very large integer from string' => [
            'type' => (string)(PHP_INT_MAX - 1),
            'value' => (string)(PHP_INT_MAX - 1),
            'expected' => PHP_INT_MAX - 1,
        ];
    }

    public function test_registered_converters_still_apply_when_casting_is_enabled(): void
    {
        // Scalar value casting adds its own converters, but the user-registered
        // ones must still be applied on top of them.
        try {
            $result = $this->mapperBuilder()
                ->allowScalarValueCasting()
                ->registerConverter(fn (string $value): string => strtoupper($value))
                ->mapper()
                ->map('string', 'foo');

            self::assertSame('FOO', $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    /**
     * A value that already satisfies a broad scalar target (`scalar`,
     * `array-key`) must be handed back untouched instead of being routed
     * through the casters. Doing otherwise previously raised an uncatchable
     * `TypeError` because a caster could return a value of a different scalar
     * kind than its declared return type.
     */
    #[TestWith(['type' => 'scalar', 'value' => 5, 'expected' => 5])]
    #[TestWith(['type' => 'scalar', 'value' => 'foo', 'expected' => 'foo'])]
    #[TestWith(['type' => 'scalar', 'value' => 5.5, 'expected' => 5.5])]
    #[TestWith(['type' => 'scalar', 'value' => true, 'expected' => true])]
    #[TestWith(['type' => 'scalar', 'value' => '42', 'expected' => '42'])]
    #[TestWith(['type' => 'array-key', 'value' => '42', 'expected' => '42'])]
    #[TestWith(['type' => 'array-key', 'value' => 5, 'expected' => 5])]
    #[TestWith(['type' => 'array{s: scalar}', 'value' => ['s' => 5], 'expected' => ['s' => 5]])]
    #[TestWith(['type' => 'array{k: array-key}', 'value' => ['k' => '42'], 'expected' => ['k' => '42']])]
    public function test_already_valid_value_is_not_cast_for_scalar_supertype_target(string $type, mixed $value, mixed $expected): void
    {
        try {
            $result = $this
                ->mapperBuilder()
                ->allowScalarValueCasting()
                ->mapper()
                ->map($type, $value);

            self::assertSame($expected, $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}
