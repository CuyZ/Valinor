<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class BooleanValueTypeTest extends TestCase
{
    public function test_named_constructors_return_singleton_instances(): void
    {
        self::assertSame(BooleanValueType::true(), BooleanValueType::true());
        self::assertSame(BooleanValueType::false(), BooleanValueType::false());
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('true', BooleanValueType::true()->toString());
        self::assertSame('false', BooleanValueType::false()->toString());
    }

    #[TestWith(['accepts' => true, 'value' => true])]
    #[TestWith(['accepts' => false, 'value' => false])]
    public function test_true_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = BooleanValueType::true();

        self::assertSame($accepts, $type->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($type, $value));
    }

    #[TestWith(['accepts' => true, 'value' => false])]
    #[TestWith(['accepts' => false, 'value' => true])]
    public function test_false_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = BooleanValueType::false();

        self::assertSame($accepts, $type->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($type, $value));
    }

    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([null])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        $trueType = BooleanValueType::true();
        $falseType = BooleanValueType::false();

        self::assertFalse($trueType->accepts($value));
        self::assertFalse($falseType->accepts($value));

        self::assertFalse($this->compiledAccept($trueType, $value));
        self::assertFalse($this->compiledAccept($falseType, $value));
    }

    public function test_can_cast_boolean_value(): void
    {
        self::assertTrue(BooleanValueType::true()->canCast(true));
        self::assertTrue(BooleanValueType::false()->canCast(false));
    }

    public function test_can_cast_string_integer_value(): void
    {
        self::assertTrue(BooleanValueType::true()->canCast('1'));
        self::assertTrue(BooleanValueType::false()->canCast('0'));
    }

    public function test_can_cast_integer_value(): void
    {
        self::assertTrue(BooleanValueType::true()->canCast(1));
        self::assertTrue(BooleanValueType::false()->canCast(0));
    }

    public function test_can_cast_string_value(): void
    {
        self::assertTrue(BooleanValueType::true()->canCast('true'));
        self::assertTrue(BooleanValueType::false()->canCast('false'));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse(BooleanValueType::true()->canCast(null));
        self::assertFalse(BooleanValueType::true()->canCast(false));
        self::assertFalse(BooleanValueType::true()->canCast(42.1337));
        self::assertFalse(BooleanValueType::true()->canCast(404));
        self::assertFalse(BooleanValueType::true()->canCast('Schwifty!'));
        self::assertFalse(BooleanValueType::true()->canCast(['foo' => 'bar']));
        self::assertFalse(BooleanValueType::true()->canCast(new stdClass()));

        self::assertFalse(BooleanValueType::false()->canCast(null));
        self::assertFalse(BooleanValueType::false()->canCast(true));
        self::assertFalse(BooleanValueType::false()->canCast(42.1337));
        self::assertFalse(BooleanValueType::false()->canCast(404));
        self::assertFalse(BooleanValueType::false()->canCast('Schwifty!'));
        self::assertFalse(BooleanValueType::false()->canCast(['foo' => 'bar']));
        self::assertFalse(BooleanValueType::false()->canCast(new stdClass()));
    }

    public function test_cast_value_returns_correct_result(): void
    {
        self::assertSame(true, BooleanValueType::true()->cast(true));
        self::assertSame(true, BooleanValueType::true()->cast('1'));
        self::assertSame(true, BooleanValueType::true()->cast(1));
        self::assertSame(true, BooleanValueType::true()->cast('true'));

        self::assertSame(false, BooleanValueType::false()->cast(false));
        self::assertSame(false, BooleanValueType::false()->cast('0'));
        self::assertSame(false, BooleanValueType::false()->cast(0));
        self::assertSame(false, BooleanValueType::false()->cast('false'));
    }

    public function test_cast_invalid_value_type_to_true_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        BooleanValueType::true()->cast('foo');
    }

    public function test_cast_invalid_value_to_true_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        BooleanValueType::true()->cast(false);
    }

    public function test_cast_invalid_value_type_to_false_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        BooleanValueType::false()->cast('foo');
    }

    public function test_cast_invalid_value_to_false_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        BooleanValueType::false()->cast(true);
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue(BooleanValueType::true()->matches(BooleanValueType::true()));
        self::assertTrue(BooleanValueType::false()->matches(BooleanValueType::false()));
    }

    public function test_matches_native_boolean_type(): void
    {
        self::assertTrue(BooleanValueType::true()->matches(new NativeBooleanType()));
        self::assertTrue(BooleanValueType::false()->matches(new NativeBooleanType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(BooleanValueType::true()->matches(new MixedType()));
        self::assertTrue(BooleanValueType::false()->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_same_type(): void
    {
        $unionTypeWithTrue = new UnionType(
            new FakeType(),
            BooleanValueType::true(),
            new FakeType(),
        );

        $unionTypeWithFalse = new UnionType(
            new FakeType(),
            BooleanValueType::false(),
            new FakeType(),
        );

        self::assertTrue(BooleanValueType::true()->matches($unionTypeWithTrue));
        self::assertTrue(BooleanValueType::false()->matches($unionTypeWithFalse));
    }

    public function test_does_not_match_union_type_not_containing_same_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse(BooleanValueType::true()->matches($unionType));
        self::assertFalse(BooleanValueType::false()->matches($unionType));
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
