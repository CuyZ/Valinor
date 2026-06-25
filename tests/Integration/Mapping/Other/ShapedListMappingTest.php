<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ShapedListMappingTest extends IntegrationTestCase
{
    public function test_values_are_mapped_properly(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map('list{string, int, float}', ['foo', 42, 1337.404]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result[0]);
        self::assertSame(42, $result[1]);
        self::assertSame(1337.404, $result[2]);
    }

    public function test_mapping_from_iterable_to_shaped_list_works_properly(): void
    {
        $iterator = (static function () {
            yield 'foo';
            yield 42;
            yield 1337.404;
        })();

        try {
            $result = $this->mapperBuilder()->mapper()->map('list{string, int, float}', $iterator);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result[0]);
        self::assertSame(42, $result[1]);
        self::assertSame(1337.404, $result[2]);
    }

    public function test_optional_element_can_be_absent(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map('list{0: string, 1?: int}', ['foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result[0]);
        self::assertCount(1, $result);
    }

    public function test_optional_element_can_be_present(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map('list{0: string, 1?: int}', ['foo', 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result[0]);
        // @phpstan-ignore offsetAccess.notFound
        self::assertSame(42, $result[1]);
    }

    public function test_unsealed_list_accepts_extra_elements(): void
    {
        try {
            /** @var array<int, mixed> $result */
            $result = $this->mapperBuilder()->mapper()->map('list{string, int, ...list<float>}', ['foo', 42, 1.5, 2.5]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result[0]);
        self::assertSame(42, $result[1]);
        self::assertSame(1.5, $result[2]);
        self::assertSame(2.5, $result[3]);
    }

    public function test_unsealed_list_without_type_accepts_any_extra_elements_with_permissive_types(): void
    {
        try {
            /**
             * @var array<int, mixed> $result
             * @phpstan-ignore varTag.type
             */
            $result = $this->mapperBuilder()
                ->allowPermissiveTypes()
                ->mapper()
                ->map('list{string, ...}', ['foo', 42, true]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result[0]);
        self::assertSame(42, $result[1]);
        self::assertTrue($result[2]);
    }

    public function test_non_sequential_source_throws_mapping_error(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('list{string, int}', [5 => 'foo', 99 => 42]);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '0' => "[invalid_string] Value *missing* is not a valid string.",
                '1' => "[invalid_integer] Value *missing* is not a valid integer.",
                '5' => "[unexpected_key] Unexpected key `5`.",
                '99' => "[unexpected_key] Unexpected key `99`.",
            ]);
            return;
        }

        self::fail('Expected a MappingError to be thrown for non-sequential source.');
    }

    public function test_missing_required_element_throws_error(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('list{string, int}', ['foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '1' => "[invalid_integer] Value *missing* is not a valid integer.",
            ]);
        }
    }

    public function test_wrong_type_at_required_position_throws_error(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('list{string, int}', [42, 'hello']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '0' => "[invalid_string] Value 42 is not a valid string.",
                '1' => "[invalid_integer] Value 'hello' is not a valid integer.",
            ]);
        }
    }

    public function test_superfluous_values_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('list{string, int}', ['foo', 42, 'extra']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '2' => '[unexpected_key] Unexpected key `2`.',
            ]);
        }
    }

    public function test_unsealed_list_keeps_processing_after_unexpected_extra_key(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(
                'list{string, ...list<float>}',
                [
                    0 => 'foo',
                    5 => 'unexpected',
                    1 => 'not-a-float',
                    99 => 'still-unexpected',
                    2 => 2.5,
                ],
            );
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '5' => '[unexpected_key] Unexpected key `5`.',
                '1' => "[invalid_float] Value 'not-a-float' is not a valid float.",
                '99' => '[unexpected_key] Unexpected key `99`.',
            ]);
            return;
        }

        self::fail('Expected a MappingError to be thrown for invalid unsealed list extras.');
    }

    public function test_mapping_into_object_with_shaped_list_property(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map(ObjectWithShapedListProperty::class, [
                'values' => ['foo', 42, 1.5],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->values[0]);
        self::assertSame(42, $result->values[1]);
        self::assertSame(1.5, $result->values[2]);
    }
}

final readonly class ObjectWithShapedListProperty
{
    public function __construct(
        /** @var list{string, int, float} */
        public array $values,
    ) {}
}
