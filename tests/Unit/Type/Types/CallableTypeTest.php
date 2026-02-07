<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\CallableType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

final class CallableTypeTest extends UnitTestCase
{
    private CallableType $callableType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->callableType = CallableType::default();
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->callableType->accepts(fn () => null));
        self::assertTrue($this->compiledAccept($this->callableType, fn () => null));
    }

    #[TestWith([true])]
    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->callableType->accepts($value));
        self::assertFalse($this->compiledAccept($this->callableType, $value));
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('callable', $this->callableType->toString());
    }

    public function test_matches_correct_callable(): void
    {
        $callableTypeA = new CallableType(
            [new NonEmptyStringType(), new PositiveIntegerType()],
            new NonEmptyStringType(),
        );

        $callableTypeB = new CallableType(
            [new NativeStringType(), new NativeIntegerType()],
            new NativeStringType(),
        );

        self::assertTrue($callableTypeA->matches($callableTypeB));

    }

    public function test_does_not_match_callable_with_non_matching_parameters(): void
    {
        $callableTypeA = new CallableType(
            [new NonEmptyStringType()],
            new NonEmptyStringType(),
        );

        $callableTypeB = new CallableType(
            [new NativeBooleanType()],
            new NativeStringType(),
        );

        self::assertFalse($callableTypeA->matches($callableTypeB));

    }

    public function test_matches_callable_with_less_parameters(): void
    {
        $callableTypeA = new CallableType(
            [new NonEmptyStringType(), new PositiveIntegerType()],
            new NonEmptyStringType(),
        );

        $callableTypeB = new CallableType(
            [new NativeStringType()],
            new NativeStringType(),
        );

        self::assertTrue($callableTypeA->matches($callableTypeB));
    }

    public function test_does_not_match_callable_with_more_parameters(): void
    {
        $callableTypeA = new CallableType(
            [new NonEmptyStringType(), new PositiveIntegerType()],
            new NonEmptyStringType(),
        );

        $callableTypeB = new CallableType(
            [new NativeStringType(), new NativeIntegerType(), new NativeBooleanType()],
            new NativeStringType(),
        );

        self::assertFalse($callableTypeA->matches($callableTypeB));

    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->callableType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->callableType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_callable_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            CallableType::default(),
            new FakeType(),
        );

        self::assertTrue($this->callableType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_boolean_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->callableType->matches($unionType));
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();
        $typeC = new FakeType();

        $callableType = new CallableType(
            [$typeA, $typeB],
            $typeC,
        );

        self::assertSame([$typeA, $typeB, $typeC], $callableType->traverse());
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('callable', $this->callableType->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
