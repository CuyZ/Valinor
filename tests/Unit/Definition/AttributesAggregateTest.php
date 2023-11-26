<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\AttributesAggregate;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AttributesAggregateTest extends TestCase
{
    public function test_aggregate_is_countable(): void
    {
        $aggregate = new AttributesAggregate(
            new FakeAttributes(new stdClass()),
            new FakeAttributes(new DateTime(), new DateTimeImmutable())
        );

        self::assertCount(3, $aggregate);
    }

    public function test_aggregate_finds_instances_in_sub_attributes(): void
    {
        $aggregate = new AttributesAggregate(
            new FakeAttributes(new stdClass()),
            new FakeAttributes(new DateTimeImmutable())
        );

        self::assertTrue($aggregate->has(stdClass::class));
        self::assertTrue($aggregate->has(DateTimeImmutable::class));
        self::assertFalse($aggregate->has(DateTime::class));
    }

    public function test_aggregate_can_filter_on_sub_attributes(): void
    {
        $objectA = new stdClass();
        $objectB = new DateTimeImmutable();
        $aggregate = new AttributesAggregate(
            new FakeAttributes($objectA),
            new FakeAttributes($objectB)
        );

        self::assertSame([$objectA], $aggregate->ofType(stdClass::class));
        self::assertSame([$objectB], $aggregate->ofType(DateTimeImmutable::class));
    }

    public function test_aggregate_is_traversable(): void
    {
        $objectA = new stdClass();
        $objectB = new stdClass();
        $objectC = new stdClass();
        $objectD = new stdClass();
        $aggregate = new AttributesAggregate(
            new FakeAttributes($objectA, $objectB),
            new FakeAttributes($objectC, $objectD)
        );

        self::assertSame([$objectA, $objectB, $objectC, $objectD], iterator_to_array($aggregate));
    }
}
