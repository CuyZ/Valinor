<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\AttributesContainer;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AttributesContainerTest extends TestCase
{
    public function test_attributes_are_countable(): void
    {
        $attributes = new AttributesContainer(new stdClass(), new stdClass(), new stdClass());

        self::assertCount(3, $attributes);
    }

    public function test_attributes_are_traversable(): void
    {
        self::assertIsIterable(new AttributesContainer());
    }

    public function test_attributes_has_type_checks_all_attributes(): void
    {
        $attributes = new AttributesContainer(new stdClass());

        self::assertTrue($attributes->has(stdClass::class));
        self::assertFalse($attributes->has(DateTimeInterface::class));
    }

    public function test_attributes_of_type_filters_on_given_class_name(): void
    {
        $object = new stdClass();
        $date = new DateTime();

        $attributes = new AttributesContainer($object, $date);
        $filteredAttributes = $attributes->ofType(DateTimeInterface::class);

        self::assertNotSame($filteredAttributes, $attributes);
        self::assertContainsEquals($date, $filteredAttributes);
        self::assertNotContains($object, $filteredAttributes);
    }
}
