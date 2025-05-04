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
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NativeFloatTypeTest extends TestCase
{
    use TestIsSingleton;

    private NativeFloatType $floatType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->floatType = new NativeFloatType();
    }

    #[TestWith([42.1337])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->floatType->accepts($value));
        self::assertTrue($this->compiledAccept($this->floatType, $value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->floatType->accepts($value));
        self::assertFalse($this->compiledAccept($this->floatType, $value));
    }

    public function test_can_cast_float_value(): void
    {
        self::assertTrue($this->floatType->canCast(404));
        self::assertTrue($this->floatType->canCast(42.1337));
        self::assertTrue($this->floatType->canCast('42.1337'));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->floatType->canCast(null));
        self::assertFalse($this->floatType->canCast(['foo' => 'bar']));
        self::assertFalse($this->floatType->canCast('Schwifty!'));
        self::assertFalse($this->floatType->canCast(false));
        self::assertFalse($this->floatType->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, float $expected): void
    {
        self::assertSame($expected, $this->floatType->cast($value));
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'Float from integer' => [
                'value' => 404,
                'expected' => 404.00,
            ],
            'Float from string' => [
                'value' => '42.1337',
                'expected' => 42.1337,
            ],
            'Float from float' => [
                'value' => 42.1337,
                'expected' => 42.1337,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->floatType->cast('foo');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('float', $this->floatType->toString());
    }

    public function test_matches_valid_types(): void
    {
        $floatTypeA = new NativeFloatType();
        $floatTypeB = new NativeFloatType();

        self::assertTrue($floatTypeA->matches($floatTypeB));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->floatType->matches(new FakeType()));
    }

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue($this->floatType->matches(new ScalarConcreteType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->floatType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_float_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NativeFloatType(),
            new FakeType(),
        );

        self::assertTrue($this->floatType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_float_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->floatType->matches($unionType));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('float', (new NativeFloatType())->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
