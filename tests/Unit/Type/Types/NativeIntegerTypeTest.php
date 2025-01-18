<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NativeIntegerTypeTest extends TestCase
{
    use TestIsSingleton;

    private NativeIntegerType $integerType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integerType = new NativeIntegerType();
    }

    #[TestWith([404])]
    #[TestWith([-404])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->integerType->accepts($value));
        self::assertTrue($this->compiledAccept($this->integerType, $value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([-42.1337])]
    #[TestWith([42.1337])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->integerType->accepts($value));
        self::assertFalse($this->compiledAccept($this->integerType, $value));
    }

    public function test_can_cast_integer_value(): void
    {
        self::assertTrue($this->integerType->canCast(-404));
        self::assertTrue($this->integerType->canCast('-404'));
        self::assertTrue($this->integerType->canCast(-404.00));
        self::assertTrue($this->integerType->canCast(404));
        self::assertTrue($this->integerType->canCast('404'));
        self::assertTrue($this->integerType->canCast(404.00));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->integerType->canCast(null));
        self::assertFalse($this->integerType->canCast(-42.1337));
        self::assertFalse($this->integerType->canCast(42.1337));
        self::assertFalse($this->integerType->canCast(['foo' => 'bar']));
        self::assertFalse($this->integerType->canCast('Schwifty!'));
        self::assertFalse($this->integerType->canCast(false));
        self::assertFalse($this->integerType->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, int $expected): void
    {
        self::assertSame($expected, $this->integerType->cast($value));
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'Negative integer from float' => [
                'value' => -404.00,
                'expected' => -404,
            ],
            'Negative integer from string' => [
                'value' => -'42',
                'expected' => -42,
            ],
            'Negative integer from integer' => [
                'value' => -1337,
                'expected' => -1337,
            ],
            'Positive integer from float' => [
                'value' => 404.00,
                'expected' => 404,
            ],
            'Positive integer from string' => [
                'value' => '42',
                'expected' => 42,
            ],
            'Positive integer from integer' => [
                'value' => 1337,
                'expected' => 1337,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->integerType->cast('foo');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('int', $this->integerType->toString());
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new NativeIntegerType())->matches(new NativeIntegerType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->integerType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->integerType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_integer_type(): void
    {
        $union = new UnionType(new FakeType(), new NativeIntegerType(), new FakeType());

        self::assertTrue($this->integerType->matches($union));
    }

    public function test_does_not_match_union_type_not_containing_integer_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->integerType->matches($unionType));
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
