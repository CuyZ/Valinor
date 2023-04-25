<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\City;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

/**
 * @requires PHP >= 8.1
 */
final class UnionMappingTest extends IntegrationTest
{
    public function test_union_with_int_or_object(): void
    {
        try {
            $array = (new MapperBuilder())->mapper()->map("list<int|" . SimpleObject::class . ">", [123, "foo"]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(123, $array[0]);
        self::assertInstanceOf(SimpleObject::class, $array[1]);
    }

    public function test_union_with_string_or_object_prioritizes_string(): void
    {
        try {
            $array = (new MapperBuilder())
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
            $array = (new MapperBuilder())
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
            $array = (new MapperBuilder())
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
}
