<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class BooleanValueTypeTest extends UnitTestCase
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

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue(BooleanValueType::true()->matches(new ScalarConcreteType()));
        self::assertTrue(BooleanValueType::false()->matches(new ScalarConcreteType()));
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

    public function test_native_type_is_correct(): void
    {
        self::assertSame('bool', BooleanValueType::true()->nativeType()->toString());
        self::assertSame('bool', BooleanValueType::false()->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
