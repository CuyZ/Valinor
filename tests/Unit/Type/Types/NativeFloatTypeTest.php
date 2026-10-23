<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class NativeFloatTypeTest extends UnitTestCase
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
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
