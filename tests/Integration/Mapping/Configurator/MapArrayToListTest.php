<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use ArrayIterator;
use CuyZ\Valinor\Mapper\Configurator\MapArrayToList;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class MapArrayToListTest extends IntegrationTestCase
{
    public function test_attribute_maps_associative_array_to_list(): void
    {
        $class = new class () {
            /** @var list<string> */
            #[MapArrayToList]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], $result->value);
    }

    public function test_attribute_maps_associative_array_to_list_on_promoted_property(): void
    {
        $class = new class ([]) {
            /**
             * @param list<string> $value
             */
            public function __construct(
                #[MapArrayToList]
                public array $value,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], $result->value);
    }

    public function test_attribute_maps_associative_array_to_non_empty_list(): void
    {
        $class = new class () {
            /** @var non-empty-list<string> */
            #[MapArrayToList]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => ['a' => 'foo', 'b' => 'bar']]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], $result->value);
    }

    public function test_maps_associative_array_to_non_empty_list_globally(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('non-empty-list<string>', ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], $result);
    }

    public function test_maps_associative_array_to_nullable_list_globally(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('list<string>|null', ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], $result);
    }

    public function test_maps_associative_array_to_shaped_list_globally(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('list{string, int}', ['a' => 'foo', 'b' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 42], $result);
    }

    public function test_maps_array_iterator_to_list_globally(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('list<string>', new ArrayIterator(['a' => 'foo', 'b' => 'bar']));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], $result);
    }

    public function test_empty_array_for_non_empty_list_target_raises_error(): void
    {
        try {
            $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('non-empty-list<string>', []);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $error) {
            self::assertMappingErrors($error, [
                '*root*' => "[value_is_empty_list] Cannot be empty and must be filled with a value matching `non-empty-list<string>`.",
            ]);
        }
    }

    public function test_non_iterable_value_for_list_target_raises_error(): void
    {
        try {
            $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('list<string>', 'foo');

            self::fail('No mapping error when one was expected');
        } catch (MappingError $error) {
            self::assertMappingErrors($error, [
                '*root*' => "[value_is_not_iterable] Value 'foo' does not match `list<string>`.",
            ]);
        }
    }

    public function test_array_target_keeps_its_keys(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('array<string>', ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['a' => 'foo', 'b' => 'bar'], $result);
    }

    public function test_non_empty_array_target_keeps_its_keys(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('non-empty-array<string>', ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['a' => 'foo', 'b' => 'bar'], $result);
    }

    public function test_iterable_target_keeps_its_keys(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('iterable<string>', ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['a' => 'foo', 'b' => 'bar'], $result);
    }

    #[DataProvider('permissive_target_keeps_its_keys_data_provider')]
    public function test_permissive_target_keeps_its_keys(string $type, mixed $value, mixed $expected): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->allowPermissiveTypes()
                ->mapper()
                ->map($type, $value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($expected, $result);
    }

    public static function permissive_target_keeps_its_keys_data_provider(): iterable
    {
        yield 'array' => [
            'type' => 'array',
            'value' => ['a' => 'foo', 'b' => 'bar'],
            'expected' => ['a' => 'foo', 'b' => 'bar'],
        ];

        yield 'array of mixed' => [
            'type' => 'array<mixed>',
            'value' => ['a' => 'foo', 'b' => 'bar'],
            'expected' => ['a' => 'foo', 'b' => 'bar'],
        ];

        yield 'array with array-key and mixed' => [
            'type' => 'array<array-key, mixed>',
            'value' => ['a' => 'foo', 'b' => 'bar'],
            'expected' => ['a' => 'foo', 'b' => 'bar'],
        ];

        yield 'sparse array of int keys and mixed' => [
            'type' => 'array<int, mixed>',
            'value' => [1 => 'foo', 3 => 'bar'],
            'expected' => [1 => 'foo', 3 => 'bar'],
        ];

        yield 'iterable of mixed' => [
            'type' => 'iterable<mixed>',
            'value' => ['a' => 'foo', 'b' => 'bar'],
            'expected' => ['a' => 'foo', 'b' => 'bar'],
        ];

        yield 'mixed' => [
            'type' => 'mixed',
            'value' => ['a' => 'foo', 'b' => 'bar'],
            'expected' => ['a' => 'foo', 'b' => 'bar'],
        ];

        yield 'mixed leaf in shaped array' => [
            'type' => 'array{value: mixed}',
            'value' => ['value' => ['a' => 'foo']],
            'expected' => ['value' => ['a' => 'foo']],
        ];

        yield 'mixed leaf in list' => [
            'type' => 'list<mixed>',
            'value' => ['a' => ['b' => 'foo']],
            'expected' => [['b' => 'foo']],
        ];
    }

    public function test_mixed_target_keeps_array_iterator_untouched(): void
    {
        $iterator = new ArrayIterator(['a' => 'foo', 'b' => 'bar']);

        try {
            $result = $this->mapperBuilder()
                ->allowNonSequentialList()
                ->allowPermissiveTypes()
                ->mapper()
                ->map('mixed', $iterator);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($iterator, $result);
    }

    public function test_invalid_values_in_associative_array_are_reported_at_list_indexes(): void
    {
        try {
            $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('list<int>', ['a' => 'foo', 'b' => 42]);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $error) {
            self::assertMappingErrors($error, [
                '0' => "[invalid_integer] Value 'foo' is not a valid integer.",
            ]);
        }
    }

    public function test_attribute_on_array_property_is_not_applied(): void
    {
        $class = new class () {
            /** @var array<string> */
            #[MapArrayToList]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['a' => 'foo', 'b' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['a' => 'foo', 'b' => 'bar'], $result->value);
    }

    public function test_attribute_maps_sparse_array_to_list(): void
    {
        $class = new class () {
            /** @var list<int> */
            #[MapArrayToList]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [2 => 10, 5 => 20, 9 => 30]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([10, 20, 30], $result->value);
    }

    public function test_configurator_maps_array_to_list_globally(): void
    {
        $class = new class () {
            public string $name;

            /** @var list<string> */
            public array $tags;
        };

        try {
            $result = $this->mapperBuilder()
                ->configureWith(new MapArrayToList())
                ->mapper()
                ->map($class::class, [
                    'name' => 'John Doe',
                    'tags' => ['a' => 'foo', 'b' => 'bar'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('John Doe', $result->name);
        self::assertSame(['foo', 'bar'], $result->tags);
    }
}
