<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NumericStringType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

final class NumericStringTypeTest extends UnitTestCase
{
    use TestIsSingleton;

    private NumericStringType $numericStringType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->numericStringType = new NumericStringType();
    }

    #[TestWith(['777.7'])]
    #[TestWith(['0'])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->numericStringType->accepts($value));
        self::assertTrue($this->compiledAccept($this->numericStringType, $value));
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->numericStringType->accepts($value));
        self::assertFalse($this->compiledAccept($this->numericStringType, $value));
    }

    public function test_can_cast_stringable_value(): void
    {
        self::assertTrue($this->numericStringType->canCast(42.1337));
        self::assertTrue($this->numericStringType->canCast(404));
        self::assertTrue($this->numericStringType->canCast(new StringableObject('1337')));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->numericStringType->canCast('Schwifty!'));
        self::assertFalse($this->numericStringType->canCast(null));
        self::assertFalse($this->numericStringType->canCast(['foo' => 'bar']));
        self::assertFalse($this->numericStringType->canCast(false));
        self::assertFalse($this->numericStringType->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, string $expected): void
    {
        self::assertSame($expected, $this->numericStringType->cast($value));
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'String from float' => [
                'value' => 404.42,
                'expected' => '404.42',
            ],
            'String from integer' => [
                'value' => 42,
                'expected' => '42',
            ],
            'String from object' => [
                'value' => new StringableObject('700'),
                'expected' => '700',
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->numericStringType->cast(new stdClass());
    }

    public function test_cast_invalid_numeric_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->numericStringType->cast('qqq');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('numeric-string', $this->numericStringType->toString());
    }

    public function test_matches_same_type(): void
    {
        $numericStringTypeA = new NumericStringType();
        $numericStringTypeB = new NumericStringType();

        self::assertTrue($numericStringTypeA->matches($numericStringTypeB));
    }

    public function test_matches_native_string_type(): void
    {
        self::assertTrue($this->numericStringType->matches(new NativeStringType()));
    }

    public function test_matches_non_empty_string_type(): void
    {
        self::assertTrue($this->numericStringType->matches(new NonEmptyStringType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->numericStringType->matches(new FakeType()));
    }

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue($this->numericStringType->matches(new ScalarConcreteType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->numericStringType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_string_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NumericStringType(),
            new FakeType(),
        );

        self::assertTrue($this->numericStringType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_string_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->numericStringType->matches($unionType));
    }

    public function test_matches_default_array_key_type(): void
    {
        self::assertTrue($this->numericStringType->matches(ArrayKeyType::default()));
    }

    public function test_matches_array_key_type_with_string_type(): void
    {
        self::assertTrue($this->numericStringType->matches(ArrayKeyType::string()));
    }

    public function test_does_not_match_array_key_type_with_integer_type(): void
    {
        self::assertFalse($this->numericStringType->matches(ArrayKeyType::integer()));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('string', (new NumericStringType())->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
