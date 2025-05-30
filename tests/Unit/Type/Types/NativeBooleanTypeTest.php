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
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NativeBooleanTypeTest extends TestCase
{
    use TestIsSingleton;

    private NativeBooleanType $booleanType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->booleanType = new NativeBooleanType();
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->booleanType->accepts($value));
        self::assertTrue($this->compiledAccept($this->booleanType, $value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->booleanType->accepts($value));
        self::assertFalse($this->compiledAccept($this->booleanType, $value));
    }

    public function test_can_cast_boolean_value(): void
    {
        self::assertTrue($this->booleanType->canCast(false));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->booleanType->canCast(null));
        self::assertFalse($this->booleanType->canCast(42.1337));
        self::assertFalse($this->booleanType->canCast(404));
        self::assertFalse($this->booleanType->canCast('Schwifty!'));
        self::assertFalse($this->booleanType->canCast(['foo' => 'bar']));
        self::assertFalse($this->booleanType->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, bool $expected): void
    {
        $result = $this->booleanType->cast($value);

        self::assertSame($expected, $result);
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'True from integer-string' => [
                'value' => '1',
                'expected' => true,
            ],
            'False from integer-string' => [
                'value' => '0',
                'expected' => false,
            ],
            'True from integer' => [
                'value' => 1,
                'expected' => true,
            ],
            'False from integer' => [
                'value' => 0,
                'expected' => false,
            ],
            'True from string' => [
                'value' => 'true',
                'expected' => true,
            ],
            'False from string' => [
                'value' => 'false',
                'expected' => false,
            ],
            'True from boolean' => [
                'value' => true,
                'expected' => true,
            ],
            'False from boolean' => [
                'value' => false,
                'expected' => false,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->booleanType->cast('foo');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('bool', $this->booleanType->toString());
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new NativeBooleanType())->matches(new NativeBooleanType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->booleanType->matches(new FakeType()));
    }

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue($this->booleanType->matches(new ScalarConcreteType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->booleanType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_boolean_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NativeBooleanType(),
            new FakeType(),
        );

        self::assertTrue($this->booleanType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_boolean_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->booleanType->matches($unionType));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('bool', (new NativeBooleanType())->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
