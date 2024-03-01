<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ListConstructorMappingTest extends IntegrationTestCase
{
    public function test_it_will_deserialize_a_hashmap_into_an_object_with_a_list_as_constructor_argument(): void
    {
        $mapper = $this
            ->mapperBuilder()
            ->allowPermissiveTypes()
            ->allowSuperfluousKeys()
            ->mapper();

        self::assertEquals(
            [7, 8, 9],
            $mapper->map(
                ClassWithSingleListConstructorArgument::class,
                [
                    'values' => [7, 8, 9],
                ]
            )->values
        );
    }

    public function test_it_will_deserialize_a_hashmap_with_additional_keys_into_an_object_with_a_list_as_constructor_argument(): void
    {
        $mapper = $this
            ->mapperBuilder()
            ->allowPermissiveTypes()
            ->allowSuperfluousKeys()
            ->mapper();

        self::assertSame(
            [7, 8, 9],
            $mapper->map(
                ClassWithSingleListConstructorArgument::class,
                [
                    'unrelated_key' => 'this-should-be-ignored-and-have-no-effect',
                    'values'        => [7, 8, 9],
                ]
            )->values
        );
    }

    public function test_it_will_deserialize_a_list_into_an_object_with_a_list_as_constructor_argument(): void
    {
        $mapper = $this
            ->mapperBuilder()
            ->allowPermissiveTypes()
            ->allowSuperfluousKeys()
            ->mapper();

        self::assertEquals(
            [7, 8, 9],
            $mapper->map(
                ClassWithSingleListConstructorArgument::class,
                [7, 8, 9]
            )->values
        );
    }
}

final class ClassWithSingleListConstructorArgument
{
    /** @param list<int> $values */
    public function __construct(public readonly array $values) {}
}
