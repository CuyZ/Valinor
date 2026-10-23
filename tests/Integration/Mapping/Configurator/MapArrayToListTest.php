<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use ArrayIterator;
use CuyZ\Valinor\Mapper\Configurator\MapArrayToList;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

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

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            self::assertSame('*root*', $error->messages()->toArray()[0]->path());
        }
    }

    public function test_non_iterable_value_for_list_target_raises_error(): void
    {
        try {
            $this->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('list<string>', 'foo');

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            self::assertSame('*root*', $error->messages()->toArray()[0]->path());
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
