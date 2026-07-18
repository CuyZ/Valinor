<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\AbstractObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

final class GenericTypesInAnonymousClassConstructorTest extends IntegrationTestCase
{
    public function test_deserializes_generic_type(): void
    {
        $result = $this->mapperBuilder()
            ->mapper()
            ->map(
                (new class (new DummyPaginable(null, [])) {
                    /** @param DummyPaginable<User> $wrapped */
                    public function __construct(public DummyPaginable $wrapped) {}
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
                    public function __construct(public DummyPaginable $wrapped) {}
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

    public function test_deserializes_generic_type_in_property(): void
    {
        $result = $this->mapperBuilder()
            ->mapper()
            ->map(
                (new class () {
                    /** @var DummyPaginable<User> */
                    public DummyPaginable $wrapped;
                })::class,
                [
                    'wrapped' => [
                        'nextPage' => 'abc123',
                        'items' => [
                            [
                                'username' => 'ocramius'
                            ]
                        ]
                    ]
                ]
            );

        self::assertEquals(
            new DummyPaginable('abc123', [new User('ocramius')]),
            $result->wrapped,
        );
    }

    public function test_deserializes_generic_type_when_anonymous_class_extends_class_from_another_namespace(): void
    {
        // The name of an anonymous class extending another class is prefixed
        // with that class' name, so the synthetic name carries the parent's
        // namespace instead of the lexical one.
        $result = $this->mapperBuilder()
            ->mapper()
            ->map(
                (new class (new DummyPaginable(null, [])) extends AbstractObject {
                    /** @param DummyPaginable<User> $wrapped */
                    public function __construct(public DummyPaginable $wrapped) {}
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

    public function test_deserializes_generic_type_with_imported_type(): void
    {
        $result = $this->mapperBuilder()
            ->mapper()
            ->map(
                (new class (new DummyPaginable(null, [])) {
                    /** @param DummyPaginable<SimpleObject> $wrapped */
                    public function __construct(public DummyPaginable $wrapped) {}
                })::class,
                [
                    'nextPage' => 'abc123',
                    'items' => [
                        [
                            'value' => 'foo'
                        ]
                    ]
                ]
            );

        self::assertSame('abc123', $result->wrapped->nextPage);
        self::assertCount(1, $result->wrapped->items);
        self::assertSame('foo', $result->wrapped->items[0]->value);
    }
}

/** @template Paged of object */
final readonly class DummyPaginable
{
    /** @param list<Paged> $items */
    public function __construct(
        public string|null $nextPage,
        public array $items,
    ) {}
}

final readonly class User
{
    public function __construct(public string $username) {}
}
