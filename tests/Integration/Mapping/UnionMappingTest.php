<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\City;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObjectWithConstructor;

final class UnionMappingTest extends IntegrationTestCase
{
    public function test_union_with_int_or_object(): void
    {
        try {
            $array = $this->mapperBuilder()->mapper()->map("list<int|" . SimpleObject::class . ">", [123, "foo"]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(123, $array[0]);
        self::assertInstanceOf(SimpleObject::class, $array[1]);
    }

    public function test_union_with_string_or_object_prioritizes_string(): void
    {
        try {
            $array = $this->mapperBuilder()
                ->mapper()
                ->map("list<string|" . SimpleObject::class . ">", ["foo", "bar", "baz"]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(["foo", "bar", "baz"], $array);
    }

    public function test_union_with_string_literal_or_object_prioritizes_string_literal(): void
    {
        try {
            $array = $this->mapperBuilder()
                ->mapper()
                ->map("list<'foo'|" . SimpleObject::class . "|'bar'>", ["foo", "bar", "baz"]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame("foo", $array[0]);
        self::assertSame("bar", $array[1]);
        self::assertInstanceOf(SimpleObject::class, $array[2]);
    }

    public function test_union_of_objects(): void
    {
        try {
            $array = $this->mapperBuilder()
                ->mapper()
                ->map(
                    "list<" . SimpleObject::class . "|" . City::class . ">",
                    ["foo", ["name" => "foobar", "timeZone" => "UTC"], "baz"],
                );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SimpleObject::class, $array[0]);
        self::assertInstanceOf(City::class, $array[1]);
        self::assertInstanceOf(SimpleObject::class, $array[2]);
    }

    /**
     * @dataProvider expectedDeSerializationForUnionHashmapTypes
     * @param array{key: SimpleObjectWithConstructor|list<SimpleObjectWithConstructor>} $expectedMap
     */
    public function test_union_of_object_and_list_types_in_hashmap_key(mixed $input, array $expectedMap): void
    {
        try {
            self::assertEquals(
                $expectedMap,
                $this->mapperBuilder()
                    ->mapper()
                    ->map(
                        "array{key:" . SimpleObjectWithConstructor::class . "|list<" . SimpleObjectWithConstructor::class . ">}",
                        $input
                    )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    /**
     * @return non-empty-array<
     *     non-empty-string,
     *     array{mixed, array{key: SimpleObjectWithConstructor|list<SimpleObjectWithConstructor>}}
     * >
     */
    public static function expectedDeSerializationForUnionHashmapTypes(): array
    {
        return [
            'single hashmap scalar value gets de-serialized into a hashmap containing a single value'       => [
                ['key' => 'single-value'],
                ['key' => new SimpleObjectWithConstructor('single-value')],
            ],
            'single hashmap value gets de-serialized into a hashmap containing a single value'       => [
                ['key' => ['value' => 'single-value']],
                ['key' => new SimpleObjectWithConstructor('single-value')],
            ],
            'multiple hashmap scalar values get de-serialized into a hashmap containing a a list of values' => [
                [
                    'key' => [
                        'multi-value-1',
                        'multi-value-2',
                    ],
                ],
                [
                    'key' => [
                        new SimpleObjectWithConstructor('multi-value-1'),
                        new SimpleObjectWithConstructor('multi-value-2'),
                    ],
                ],
            ],
            'multiple hashmap values get de-serialized into a hashmap containing a a list of values' => [
                [
                    'key' => [
                        ['value' => 'multi-value-1'],
                        ['value' => 'multi-value-2'],
                    ],
                ],
                [
                    'key' => [
                        new SimpleObjectWithConstructor('multi-value-1'),
                        new SimpleObjectWithConstructor('multi-value-2'),
                    ],
                ],
            ],
        ];
    }
}
