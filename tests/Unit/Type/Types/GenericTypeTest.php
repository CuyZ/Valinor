<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class GenericTypeTest extends UnitTestCase
{
    #[TestWith(['foo'])]
    public function test_accepts_correct_values(mixed $value): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertTrue($genericType->accepts($value));
        self::assertTrue($this->compiledAccept($genericType, $value));
    }

    #[TestWith([null])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertFalse($genericType->accepts($value));
        self::assertFalse($this->compiledAccept($genericType, $value));

    }

    public function test_string_value_is_correct(): void
    {
        $genericTypeOfMixed = new GenericType('T', new MixedType());
        $genericTypeOfString = new GenericType('T', new NativeStringType());

        self::assertSame('T', $genericTypeOfMixed->toString());

        self::assertSame('T of string', $genericTypeOfString->toString());

    }

    public function test_matches_same_type(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertTrue($genericType->matches(new NativeStringType()));
    }

    public function test_does_not_match_other_type(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertFalse($genericType->matches(new FakeType()));
    }

    public function test_native_type_is_correct(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertSame('string', $genericType->nativeType()->toString());
    }

    public function test_covariant_flag_is_false_by_default(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertFalse($genericType->covariant);
    }

    public function test_covariant_flag_can_be_set(): void
    {
        $genericType = new GenericType('T', new NativeStringType(), covariant: true);

        self::assertTrue($genericType->covariant);
    }

    public function test_covariant_string_value_is_correct(): void
    {
        $genericTypeOfMixed = new GenericType('T', new MixedType(), covariant: true);
        $genericTypeOfString = new GenericType('T', new NativeStringType(), covariant: true);

        self::assertSame('T', $genericTypeOfMixed->toString());
        self::assertSame('T of string', $genericTypeOfString->toString());
    }

    public function test_default_is_null_by_default(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertNull($genericType->default);
    }

    public function test_default_can_be_set(): void
    {
        $genericType = new GenericType('T', new MixedType(), default: new NativeStringType());

        self::assertSame('string', $genericType->default?->toString());
    }

    public function test_string_value_does_not_contain_default(): void
    {
        $genericTypeWithDefault = new GenericType('T', new MixedType(), default: new NativeStringType());
        $genericTypeWithBoundAndDefault = new GenericType('T', new NativeStringType(), default: new NativeStringType());

        self::assertSame('T', $genericTypeWithDefault->toString());
        self::assertSame('T of string', $genericTypeWithBoundAndDefault->toString());
    }

    public function test_template_is_inferred_from_single_union_member_within_its_bound(): void
    {
        $genericType = new GenericType('T', new NativeIntegerType());
        $union = new UnionType(new NativeIntegerType(), new NativeStringType());

        $generics = $genericType->inferGenericsFrom($union, new Generics());

        self::assertSame('int', $generics->items['T']->toString());
    }

    public function test_template_is_inferred_from_union_member_that_is_a_subtype_of_its_bound(): void
    {
        $genericType = new GenericType('T', new NativeStringType());
        $union = new UnionType(NonEmptyStringType::get(), new NativeIntegerType());

        $generics = $genericType->inferGenericsFrom($union, new Generics());

        self::assertSame('non-empty-string', $generics->items['T']->toString());
    }

    public function test_template_is_inferred_from_several_union_members_within_its_bound(): void
    {
        $genericType = new GenericType('T', UnionType::from(new NativeIntegerType(), new NativeFloatType()));
        $union = new UnionType(new NativeIntegerType(), new NativeFloatType(), new NativeStringType());

        $generics = $genericType->inferGenericsFrom($union, new Generics());

        self::assertSame('int|float', $generics->items['T']->toString());
    }

    public function test_template_is_not_inferred_from_union_without_member_within_its_bound(): void
    {
        $genericType = new GenericType('T', new NativeIntegerType());
        $union = new UnionType(new NativeStringType(), new NativeFloatType());

        $generics = $genericType->inferGenericsFrom($union, new Generics());

        self::assertSame([], $generics->items);
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
