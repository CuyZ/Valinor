<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Iterator;
use PHPUnit\Framework\TestCase;
use stdClass;

final class InterfaceTypeTest extends TestCase
{
    public function test_signature_can_be_retrieved(): void
    {
        $type = new InterfaceType(DateTimeInterface::class);

        self::assertSame(DateTimeInterface::class, $type->className());
    }

    public function test_string_value_is_correct(): void
    {
        $generic = new FakeType();
        $type = new InterfaceType(stdClass::class, ['Template' => $generic]);

        self::assertSame(stdClass::class . "<$generic>", (string)$type);
    }

    public function test_accepts_correct_values(): void
    {
        $type = new InterfaceType(DateTimeInterface::class);

        self::assertTrue($type->accepts(new DateTime()));
        self::assertTrue($type->accepts(new DateTimeImmutable()));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        $type = new InterfaceType(DateTimeInterface::class);

        self::assertFalse($type->accepts(null));
        self::assertFalse($type->accepts('Schwifty!'));
        self::assertFalse($type->accepts(42.1337));
        self::assertFalse($type->accepts(404));
        self::assertFalse($type->accepts(['foo' => 'bar']));
        self::assertFalse($type->accepts(false));
        self::assertFalse($type->accepts(new stdClass()));
    }

    public function test_matches_other_identical_interface(): void
    {
        $interfaceTypeA = new InterfaceType(DateTimeInterface::class);
        $interfaceTypeB = new InterfaceType(DateTimeInterface::class);

        self::assertTrue($interfaceTypeA->matches($interfaceTypeB));
    }

    public function test_matches_sub_class(): void
    {
        $interfaceTypeA = new InterfaceType(DateTimeInterface::class);
        $interfaceTypeB = new InterfaceType(DateTime::class);

        self::assertTrue($interfaceTypeA->matches($interfaceTypeB));
    }

    public function test_does_not_match_invalid_type(): void
    {
        self::assertFalse((new InterfaceType(DateTimeInterface::class))->matches(new FakeType()));
    }

    public function test_does_not_match_invalid_class(): void
    {
        $interfaceTypeA = new InterfaceType(DateTimeInterface::class);
        $interfaceTypeB = new InterfaceType(Iterator::class);

        self::assertFalse($interfaceTypeA->matches($interfaceTypeB));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new InterfaceType(DateTimeInterface::class))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new InterfaceType(DateTimeInterface::class),
            new FakeType(),
        );

        self::assertTrue((new InterfaceType(DateTimeInterface::class))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $interfaceType = new InterfaceType(DateTimeInterface::class);
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($interfaceType->matches($unionType));
    }

    public function test_matches_intersection_of_valid_types(): void
    {
        $intersectionType = new IntersectionType(
            new InterfaceType(DateTimeInterface::class),
            new InterfaceType(DateTime::class)
        );

        self::assertTrue((new InterfaceType(DateTimeInterface::class))->matches($intersectionType));
    }

    public function test_does_not_match_intersection_containing_invalid_type(): void
    {
        $intersectionType = new IntersectionType(
            new FakeObjectType(stdClass::class),
            new FakeObjectType(stdClass::class)
        );

        self::assertFalse((new InterfaceType(DateTime::class))->matches($intersectionType));
    }
}
