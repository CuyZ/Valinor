<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonNegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonPositiveIntegerType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class IntegerValueTypeTest extends TestCase
{
    private IntegerValueType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new IntegerValueType(1337);
    }

    public function test_value_can_be_retrieved(): void
    {
        self::assertSame(1337, $this->type->value());
    }

    #[TestWith([1337])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->type->accepts($value));
        self::assertTrue($this->compiledAccept($this->type, $value));
    }

    #[TestWith([404])]
    #[TestWith([-404])]
    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->type->accepts($value));
        self::assertFalse($this->compiledAccept($this->type, $value));
    }

    public function test_can_cast_integer_value(): void
    {
        self::assertTrue($this->type->canCast(1337));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->type->canCast(404));
        self::assertFalse($this->type->canCast('404'));
        self::assertFalse($this->type->canCast(null));
        self::assertFalse($this->type->canCast(42.1337));
        self::assertFalse($this->type->canCast(['foo' => 'bar']));
        self::assertFalse($this->type->canCast('Schwifty!'));
        self::assertFalse($this->type->canCast(false));
        self::assertFalse($this->type->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, int $expected): void
    {
        self::assertSame($expected, $this->type->cast($value));
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'Integer from float' => [
                'value' => 1337.00,
                'expected' => 1337,
            ],
            'Integer from string' => [
                'value' => '1337',
                'expected' => 1337,
            ],
            'Integer from integer' => [
                'value' => 1337,
                'expected' => 1337,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->type->cast('foo');
    }

    public function test_cast_another_integer_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->type->cast('42');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('1337', $this->type->toString());
    }

    public function test_matches_same_type_with_same_value(): void
    {
        self::assertTrue((new IntegerValueType(1337))->matches(new IntegerValueType(1337)));
    }

    public function test_does_not_match_same_type_with_different_value(): void
    {
        self::assertFalse((new IntegerValueType(1337))->matches(new IntegerValueType(42)));
    }

    public function test_matches_positive_integer_when_value_is_positive(): void
    {
        self::assertTrue((new IntegerValueType(1337))->matches(new PositiveIntegerType()));
    }

    public function test_does_not_match_positive_integer_when_value_is_negative(): void
    {
        self::assertFalse((new IntegerValueType(-1337))->matches(new PositiveIntegerType()));
    }

    public function test_does_not_match_positive_integer_when_value_is_zero(): void
    {
        self::assertFalse((new IntegerValueType(0))->matches(new PositiveIntegerType()));
    }

    public function test_matches_negative_integer_when_value_is_negative(): void
    {
        self::assertTrue((new IntegerValueType(-1337))->matches(new NegativeIntegerType()));
    }

    public function test_does_not_match_negative_integer_when_value_is_positive(): void
    {
        self::assertFalse((new IntegerValueType(1337))->matches(new NegativeIntegerType()));
    }

    public function test_does_not_match_negative_integer_when_value_is_zero(): void
    {
        self::assertFalse((new IntegerValueType(0))->matches(new NegativeIntegerType()));
    }

    public function test_matches_non_positive_integer_when_value_is_negative(): void
    {
        self::assertTrue((new IntegerValueType(-1337))->matches(new NonPositiveIntegerType()));
    }

    public function test_matches_non_positive_integer_when_value_is_zero(): void
    {
        self::assertTrue((new IntegerValueType(0))->matches(new NonPositiveIntegerType()));
    }

    public function test_does_not_match_non_positive_integer_when_value_is_positive(): void
    {
        self::assertFalse((new IntegerValueType(1337))->matches(new NonPositiveIntegerType()));
    }

    public function test_matches_non_negative_integer_when_value_is_positive(): void
    {
        self::assertTrue((new IntegerValueType(1337))->matches(new NonNegativeIntegerType()));
    }

    public function test_matches_non_negative_integer_when_value_is_zero(): void
    {
        self::assertTrue((new IntegerValueType(0))->matches(new NonNegativeIntegerType()));
    }

    public function test_does_not_match_non_negative_integer_when_value_is_negative(): void
    {
        self::assertFalse((new IntegerValueType(-1337))->matches(new NonNegativeIntegerType()));
    }

    public function test_matches_integer_range_when_value_is_in_range(): void
    {
        self::assertTrue((new IntegerValueType(10))->matches(new IntegerRangeType(10, 20)));
        self::assertTrue((new IntegerValueType(15))->matches(new IntegerRangeType(10, 20)));
        self::assertTrue((new IntegerValueType(20))->matches(new IntegerRangeType(10, 20)));
    }

    public function test_does_not_match_integer_range_when_value_is_not_in_range(): void
    {
        self::assertFalse((new IntegerValueType(50))->matches(new IntegerRangeType(10, 20)));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->type->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->type->matches(new MixedType()));
    }

    public function test_matches_native_integer_type(): void
    {
        self::assertTrue($this->type->matches(new NativeIntegerType()));
    }

    public function test_matches_union_type_containing_integer_type(): void
    {
        $union = new UnionType(new FakeType(), new IntegerValueType(1337), new FakeType());

        self::assertTrue($this->type->matches($union));
    }

    public function test_does_not_match_union_type_not_containing_integer_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->type->matches($unionType));
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
