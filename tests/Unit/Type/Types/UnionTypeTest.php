<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\Exception\ForbiddenMixedType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class UnionTypeTest extends TestCase
{
    public function test_types_can_be_retrieved(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $types = (new UnionType($typeA, $typeB, $typeC))->types();

        self::assertSame($typeA, $types[0]);
        self::assertSame($typeB, $types[1]);
        self::assertSame($typeC, $types[2]);
    }

    public function test_create_union_with_union_merges_them(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $unionA = new UnionType($typeA, $typeB);
        $unionB = new UnionType($unionA, $typeC);

        self::assertSame(
            [$typeA, $typeB, $typeC],
            $unionB->types()
        );
    }

    public function test_create_union_with_mixed_type_throws_exception(): void
    {
        $this->expectException(ForbiddenMixedType::class);
        $this->expectExceptionCode(1608146262);
        $this->expectExceptionMessage('Type `mixed` can only be used as a standalone type and not as a union member.');

        new UnionType(new FakeType(), new MixedType());
    }

    public function test_to_string_returns_correct_value(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $unionType = new UnionType($typeA, $typeB, $typeC);

        self::assertSame("$typeA|$typeB|$typeC", (string)$unionType);
    }

    public function test_accepts_correct_values(): void
    {
        $typeA = FakeType::thatWillAccept(42.1337);
        $typeB = FakeType::thatWillAccept('foo');
        $typeC = FakeType::thatWillAccept($object = new stdClass());

        $unionType = new UnionType($typeA, $typeB, $typeC);

        self::assertTrue($unionType->accepts(42.1337));
        self::assertTrue($unionType->accepts('foo'));
        self::assertTrue($unionType->accepts($object));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $unionType = new UnionType($typeA, $typeB, $typeC);

        self::assertFalse($unionType->accepts(null));
        self::assertFalse($unionType->accepts('Schwifty!'));
        self::assertFalse($unionType->accepts(42.1337));
        self::assertFalse($unionType->accepts(404));
        self::assertFalse($unionType->accepts(['foo' => 'bar']));
        self::assertFalse($unionType->accepts(false));
        self::assertFalse($unionType->accepts(new stdClass()));
    }

    public function test_matches_valid_type(): void
    {
        $typeA = new FakeType();
        $typeB = FakeType::thatWillMatch($typeC = new FakeType());

        $unionType = new UnionType($typeA, $typeB);

        self::assertTrue($unionType->matches($typeC));
    }

    public function test_does_not_match_invalid_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $unionTypeA = new UnionType($typeA, $typeB);

        self::assertFalse($unionTypeA->matches($typeC));
    }

    public function test_matches_other_matching_union(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $unionTypeA = new UnionType($typeA, $typeC);
        $unionTypeB = new UnionType($typeA, $typeB, $typeC);

        self::assertTrue($unionTypeA->matches($unionTypeB));
    }

    public function test_does_not_match_other_not_matching_union(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $unionTypeA = new UnionType($typeA, $typeB, $typeC);
        $unionTypeB = new UnionType($typeB);

        self::assertFalse($unionTypeA->matches($unionTypeB));
    }
}
