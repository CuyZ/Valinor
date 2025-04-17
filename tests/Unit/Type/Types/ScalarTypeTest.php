<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\ScalarType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ScalarTypeTest extends TestCase
{
    use TestIsSingleton;

    private ScalarType $scalarType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scalarType = new ScalarType();
    }

    #[TestWith([42, 12.3, 'Schwifty!', true, false])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->scalarType->accepts($value));
        self::assertTrue($this->compiledAccept($this->scalarType, $value));
    }

    #[TestWith([null])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->scalarType->accepts($value));
        self::assertFalse($this->compiledAccept($this->scalarType, $value));
    }

    public function test_can_cast_scalar_or_stringable_value(): void
    {
        self::assertTrue($this->scalarType->canCast('Schwifty!'));
        self::assertTrue($this->scalarType->canCast(42.1337));
        self::assertTrue($this->scalarType->canCast(404));
        self::assertTrue($this->scalarType->canCast(new StringableObject()));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->scalarType->canCast(null));
        self::assertFalse($this->scalarType->canCast(['foo' => 'bar']));
        self::assertFalse($this->scalarType->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, bool|string|int|float $expected): void
    {
        self::assertSame($expected, $this->scalarType->cast($value));
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'Float from float' => [
                'value' => 404.42,
                'expected' => 404.42,
            ],
            'Integer from integer' => [
                'value' => 42,
                'expected' => 42,
            ],
            'String from Stringable object' => [
                'value' => new StringableObject(),
                'expected' => 'foo',
            ],
            'String from string' => [
                'value' => 'bar',
                'expected' => 'bar',
            ],
            'Boolean from boolean' => [
                'value' => true,
                'expected' => true,
            ]
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->scalarType->cast(new stdClass());
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('scalar', $this->scalarType->toString());
    }

    public function test_matches_same_type(): void
    {
        $scalarTypeA = new ScalarType();
        $scalarTypeB = new ScalarType();

        self::assertTrue($scalarTypeA->matches($scalarTypeB));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->scalarType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->scalarType->matches(new MixedType()));
    }

    public function test_matches_native_float_type(): void
    {
        self::assertTrue($this->scalarType->matches(new NativeFloatType()));
    }

    public function test_matches_native_integer_type(): void
    {
        self::assertTrue($this->scalarType->matches(new NativeIntegerType()));
    }

    public function test_matches_native_string_type(): void
    {
        self::assertTrue($this->scalarType->matches(new NativeStringType()));
    }

    public function test_matches_native_boolean_type(): void
    {
        self::assertTrue($this->scalarType->matches(new NativeBooleanType()));
    }

    public function test_matches_union_type_containing_scalar_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new ScalarType(),
            new FakeType(),
        );

        self::assertTrue($this->scalarType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_scalar_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->scalarType->matches($unionType));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('int|float|string|bool', (new ScalarType())->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
