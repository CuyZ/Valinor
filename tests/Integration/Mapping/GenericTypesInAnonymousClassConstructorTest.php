<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

/** @template Paged of object */
final readonly class DummyPaginable
{
    /** @param list<Paged> $items */
    public function __construct(
        public string|null $nextPage,
        public array $items,
    )
    {
    }
}

final readonly class User {
    public function __construct(public string $username)
    {
    }
}

final class GenericTypesInAnonymousClassConstructorTest extends IntegrationTestCase
{
    public function test_deserializes_generic_type(): void
    {
        $result = $this->mapperBuilder()
            ->mapper()
            ->map(
                (new class (new DummyPaginable(null, [])) {
                    /** @param DummyPaginable<User> $wrapped */
                    public function __construct(public DummyPaginable $wrapped)
                    {
                    }
                })::class,
                [
                    'nextPage' => 'abc123',
                    'items' => [
                        [
                            'username' => 'ocramius'
                        ]
                    ]
                ]
            );
        
        self::assertEquals(
            new DummyPaginable('abc123', [new User('ocramius')]),
            $result->wrapped,
        );
    }

    public function test_deserializes_generic_type_with_fqcn_references(): void
    {
        $result = $this->mapperBuilder()
            ->mapper()
            ->map(
                (new class (new DummyPaginable(null, [])) {
                    /** @param \CuyZ\Valinor\Tests\Integration\Mapping\DummyPaginable<\CuyZ\Valinor\Tests\Integration\Mapping\User> $wrapped */
                    public function __construct(public DummyPaginable $wrapped)
                    {
                    }
                })::class,
                [
                    'nextPage' => 'abc123',
                    'items' => [
                        [
                            'username' => 'ocramius'
                        ]
                    ]
                ]
            );

        self::assertEquals(
            new DummyPaginable('abc123', [new User('ocramius')]),
            $result->wrapped,
        );
    }
}
