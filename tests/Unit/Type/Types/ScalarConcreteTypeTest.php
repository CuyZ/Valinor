<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class ScalarConcreteTypeTest extends UnitTestCase
{
    use TestIsSingleton;

    private ScalarConcreteType $scalarType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scalarType = new ScalarConcreteType();
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

    public function test_string_value_is_correct(): void
    {
        self::assertSame('scalar', $this->scalarType->toString());
    }

    public function test_matches_same_type(): void
    {
        $scalarTypeA = new ScalarConcreteType();
        $scalarTypeB = new ScalarConcreteType();

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

    public function test_does_not_match_native_float_type(): void
    {
        self::assertFalse($this->scalarType->matches(new NativeFloatType()));
    }

    public function test_does_not_match_native_integer_type(): void
    {
        self::assertFalse($this->scalarType->matches(new NativeIntegerType()));
    }

    public function test_does_not_match_native_string_type(): void
    {
        self::assertFalse($this->scalarType->matches(new NativeStringType()));
    }

    public function test_does_not_match_native_boolean_type(): void
    {
        self::assertFalse($this->scalarType->matches(new NativeBooleanType()));
    }

    public function test_matches_union_type_containing_scalar_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new ScalarConcreteType(),
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
        self::assertSame('int|float|string|bool', (new ScalarConcreteType())->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
