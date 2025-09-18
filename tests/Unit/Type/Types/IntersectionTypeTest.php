<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestWith;
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
            $typeC,
            $typeD,
        ))->types();

        self::assertSame($typeA, $types[0]);
        self::assertSame($typeB, $types[1]);
        self::assertSame($typeC, $types[2]);
        self::assertSame($typeD, $types[3]);
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
        $typeA = FakeObjectType::accepting(stdClass::class);
        $typeB = FakeObjectType::accepting(stdClass::class);
        $typeC = FakeObjectType::accepting(stdClass::class);

        $intersectionType = new IntersectionType($typeA, $typeB, $typeC);

        self::assertTrue($intersectionType->accepts(new stdClass()));
        self::assertTrue($this->compiledAccept($intersectionType, new stdClass()));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        $intersectionType = new IntersectionType(new FakeObjectType(), new FakeObjectType());

        self::assertFalse($intersectionType->accepts($value));
        self::assertFalse($this->compiledAccept($intersectionType, $value));
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

    public function test_native_type_is_correct(): void
    {
        self::assertSame(stdClass::class . '&' . DateTimeImmutable::class, (new IntersectionType(
            new NativeClassType(stdClass::class, ['Template' => new FakeType()]),
            new FakeObjectType(DateTimeImmutable::class),
        ))->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
