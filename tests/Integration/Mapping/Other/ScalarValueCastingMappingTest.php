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
}
