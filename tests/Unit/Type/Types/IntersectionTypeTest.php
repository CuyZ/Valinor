<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeObjectCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class IntersectionTypeTest extends TestCase
{
    public function test_types_can_be_retrieved(): void
    {
        $typeA = new FakeObjectType();
        $typeB = new FakeObjectType();
        $typeC = new FakeObjectType();
        $typeD = new FakeObjectType();

        $types = (new IntersectionType(
            $typeA,
            $typeB,
            // Putting those in associative array on purpose
            ...['C' => $typeC, 'D' => $typeD],
        ))->types();

        self::assertSame($typeA, $types[0]);
        self::assertSame($typeB, $types[1]);
        self::assertSame($typeC, $types[2]);
    }

    public function test_to_string_returns_correct_value(): void
    {
        $typeA = new FakeObjectType();
        $typeB = new FakeObjectType();
        $typeC = new FakeObjectType();

        $intersectionType = new IntersectionType($typeA, $typeB, $typeC);

        self::assertSame("{$typeA->toString()}&{$typeB->toString()}&{$typeC->toString()}", $intersectionType->toString());
    }

    public function test_accepts_correct_values(): void
    {
        $object = new stdClass();

        $typeA = FakeObjectType::accepting($object);
        $typeB = FakeObjectType::accepting($object);
        $typeC = FakeObjectType::accepting($object);

        $intersectionType = new IntersectionType($typeA, $typeB, $typeC);

        self::assertTrue($intersectionType->accepts($object));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        $typeA = new FakeObjectType();
        $typeB = new FakeObjectType();
        $typeC = new FakeObjectType();

        $intersectionType = new IntersectionType($typeA, $typeB, $typeC);

        self::assertFalse($intersectionType->accepts(null));
        self::assertFalse($intersectionType->accepts('Schwifty!'));
        self::assertFalse($intersectionType->accepts(42.1337));
        self::assertFalse($intersectionType->accepts(404));
        self::assertFalse($intersectionType->accepts(['foo' => 'bar']));
        self::assertFalse($intersectionType->accepts(false));
        self::assertFalse($intersectionType->accepts(new stdClass()));
    }

    public function test_matches_valid_type(): void
    {
        $objectTypeA = new FakeObjectType();
        $objectTypeB = FakeObjectType::matching($objectTypeA);
        $objectTypeC = FakeObjectType::matching($objectTypeA);

        $intersectionType = new IntersectionType($objectTypeB, $objectTypeC);

        self::assertTrue($intersectionType->matches($objectTypeA));
    }

    public function test_does_not_match_invalid_type(): void
    {
        $objectTypeA = new FakeObjectType();
        $objectTypeB = new FakeObjectType();
        $objectTypeC = new FakeObjectType();

        $intersectionType = new IntersectionType($objectTypeA, $objectTypeB);

        self::assertFalse($intersectionType->matches($objectTypeC));
    }

    public function test_matches_mixed_type(): void
    {
        $objectTypeA = new FakeObjectType();
        $objectTypeB = new FakeObjectType();

        $intersectionType = new IntersectionType($objectTypeA, $objectTypeB);

        self::assertTrue($intersectionType->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $objectTypeA = new FakeObjectType();
        $objectTypeB = FakeObjectType::matching($objectTypeA);
        $objectTypeC = FakeObjectType::matching($objectTypeA);

        $intersectionType = new IntersectionType($objectTypeB, $objectTypeC);

        $unionType = new UnionType(
            new FakeType(),
            $objectTypeA,
            new FakeType(),
        );

        self::assertTrue($intersectionType->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $intersectionType = new IntersectionType(new FakeObjectType(), new FakeObjectType());
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($intersectionType->matches($unionType));
    }

    public function test_traverse_type_yields_sub_types(): void
    {
        $objectTypeA = new FakeObjectType();
        $objectTypeB = new FakeObjectType();

        $type = new IntersectionType($objectTypeA, $objectTypeB);

        self::assertCount(2, $type->traverse());
        self::assertContains($objectTypeA, $type->traverse());
        self::assertContains($objectTypeB, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();
        $objectTypeA = new FakeObjectCompositeType(stdClass::class, ['Template' => $subTypeA]);
        $objectTypeB = new FakeObjectCompositeType(stdClass::class, ['Template' => $subTypeB]);

        $type = new IntersectionType($objectTypeA, $objectTypeB);

        self::assertCount(4, $type->traverse());
        self::assertContains($subTypeA, $type->traverse());
        self::assertContains($subTypeB, $type->traverse());
        self::assertContains($objectTypeA, $type->traverse());
        self::assertContains($objectTypeB, $type->traverse());
    }
}
