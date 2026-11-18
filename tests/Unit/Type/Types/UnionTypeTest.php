<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Exception\Union\ForbiddenMixedType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\FloatValueType;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedListType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class UnionTypeTest extends UnitTestCase
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

        $unionA = UnionType::from($typeA, $typeB);
        $unionB = UnionType::from($unionA, $typeC);

        self::assertInstanceOf(UnionType::class, $unionB);
        self::assertSame(
            [$typeA, $typeB, $typeC],
            $unionB->types()
        );
    }

    public function test_create_union_with_mixed_type_throws_exception(): void
    {
        $this->expectException(ForbiddenMixedType::class);
        $this->expectExceptionMessage('Type `mixed` can only be used as a standalone type and not as a union member.');

        UnionType::from(new FakeType(), new MixedType());
    }

    public function test_to_string_returns_correct_value(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $unionType = new UnionType($typeA, $typeB, $typeC);

        self::assertSame("{$typeA->toString()}|{$typeB->toString()}|{$typeC->toString()}", $unionType->toString());
    }

    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith(['foo'])]
    public function test_accepts_correct_values(mixed $value): void
    {
        $typeA = new FloatValueType(42.1337);
        $typeB = new IntegerValueType(404);
        $typeC = new StringValueType('foo');

        $unionType = new UnionType($typeA, $typeB, $typeC);

        self::assertTrue($unionType->accepts($value));
        self::assertTrue($this->compiledAccept($unionType, $value));
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
        $typeA = new FloatValueType(10.50);
        $typeB = new IntegerValueType(20);
        $typeC = new StringValueType('Some value');

        $unionType = new UnionType($typeA, $typeB, $typeC);

        self::assertFalse($unionType->accepts($value));
        self::assertFalse($this->compiledAccept($unionType, $value));
    }

    public function test_matches_valid_type(): void
    {
        $subType = new FakeType();
        $parentTypeA = FakeType::matching($subType);
        $parentTypeB = FakeType::matching($subType);

        $unionType = new UnionType($parentTypeA, $parentTypeB);

        self::assertTrue($unionType->matches($subType));
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
        $typeA = new NativeStringType();
        $typeB = new NativeIntegerType();
        $typeC = new NativeFloatType();

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
        $unionTypeB = new UnionType($typeB, $typeC);

        self::assertFalse($unionTypeA->matches($unionTypeB));
    }

    public function test_traverse_type_yields_sub_types(): void
    {
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();

        $type = new UnionType($subTypeA, $subTypeB);

        self::assertSame([$subTypeA, $subTypeB], $type->traverse());
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('string|int|float', (new UnionType(
            new NativeStringType(),
            new NativeIntegerType(),
            new NativeStringType(),
            new NativeFloatType()
        ))->nativeType()->toString());
    }

    public function test_native_type_merges_members_with_same_native_type(): void
    {
        $unionType = new UnionType(
            new ListType(NativeStringType::get()),
            new ShapedListType([new ShapedArrayElement(new IntegerValueType(0), NativeIntegerType::get())]),
            NativeIntegerType::get(),
            new IntegerValueType(42),
        );

        self::assertSame('array|int', $unionType->nativeType()->toString());
    }

    public function test_native_type_of_members_with_union_native_type_is_flattened(): void
    {
        $unionType = new UnionType(NativeIntegerType::get(), ArrayKeyType::default(), NullType::get());

        self::assertSame('int|string|null', $unionType->nativeType()->toString());
    }

    public function test_native_type_of_member_with_mixed_native_type_is_mixed(): void
    {
        $unionType = new UnionType(new GenericType('T', MixedType::get()), NullType::get());

        self::assertSame(MixedType::get(), $unionType->nativeType());
    }

    public function test_native_type_of_members_with_one_native_type_is_this_native_type(): void
    {
        $unionType = new UnionType(BooleanValueType::true(), BooleanValueType::false());

        self::assertSame(NativeBooleanType::get(), $unionType->nativeType());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
