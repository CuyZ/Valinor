<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class ArrayKeyTypeTest extends UnitTestCase
{
    public function test_instances_are_memoized(): void
    {
        self::assertSame(ArrayKeyType::default(), ArrayKeyType::default());
        self::assertSame(ArrayKeyType::integer(), ArrayKeyType::integer());
        self::assertSame(ArrayKeyType::string(), ArrayKeyType::string());
        self::assertSame(ArrayKeyType::integer(), ArrayKeyType::from([new NativeIntegerType()]));
        self::assertSame(ArrayKeyType::string(), ArrayKeyType::from([new NativeStringType()]));
    }

    public function test_string_values_are_correct(): void
    {
        self::assertSame('array-key', ArrayKeyType::default()->toString());
        self::assertSame('int', ArrayKeyType::integer()->toString());
        self::assertSame('string', ArrayKeyType::string()->toString());
    }

    #[TestWith(['accepts' => true, 'value' => 42])]
    #[TestWith(['accepts' => true, 'value' => 'foo'])]
    public function test_default_array_key_type_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = ArrayKeyType::default();

        self::assertSame($accepts, $type->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($type, $value));
    }

    #[TestWith(['accepts' => true, 'value' => 42])]
    #[TestWith(['accepts' => false, 'value' => 'foo'])]
    public function test_integer_array_key_type_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = ArrayKeyType::integer();

        self::assertSame($accepts, $type->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($type, $value));
    }

    #[TestWith(['accepts' => true, 'value' => 'foo'])]
    #[TestWith(['accepts' => true, 'value' => 42])]
    public function test_string_array_key_type_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = ArrayKeyType::string();

        self::assertSame($accepts, $type->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($type, $value));
    }

    #[TestWith([null])]
    #[TestWith([42.1337])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        $defaultArrayKeyType = ArrayKeyType::default();
        $integerArrayKeyType = ArrayKeyType::integer();
        $stringArrayKeyType = ArrayKeyType::string();

        self::assertFalse($defaultArrayKeyType->accepts($value));
        self::assertFalse($integerArrayKeyType->accepts($value));
        self::assertFalse($stringArrayKeyType->accepts($value));

        self::assertFalse($this->compiledAccept($defaultArrayKeyType, $value));
        self::assertFalse($this->compiledAccept($integerArrayKeyType, $value));
        self::assertFalse($this->compiledAccept($stringArrayKeyType, $value));
    }

    public function test_string_value_key_accepts_correct_value(): void
    {
        $type = new ArrayKeyType([new StringValueType('foo')]);

        self::assertTrue($type->accepts('foo'));
        self::assertTrue($this->compiledAccept($type, 'foo'));
    }

    #[TestWith([null])]
    #[TestWith([404])]
    #[TestWith([42.1337])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_string_value_key_does_not_accept_incorrect_value(mixed $value): void
    {
        $type = new ArrayKeyType([new StringValueType('foo')]);

        self::assertFalse($type->accepts($value));
        self::assertFalse($this->compiledAccept($type, $value));
    }

    public function test_matches_each_others(): void
    {
        $arrayKeyDefault = ArrayKeyType::default();
        $arrayKeyInteger = ArrayKeyType::integer();
        $arrayKeyString = ArrayKeyType::string();

        self::assertTrue($arrayKeyDefault->matches($arrayKeyDefault));
        self::assertFalse($arrayKeyDefault->matches($arrayKeyInteger));
        self::assertFalse($arrayKeyDefault->matches($arrayKeyString));

        self::assertTrue($arrayKeyInteger->matches($arrayKeyDefault));
        self::assertTrue($arrayKeyInteger->matches($arrayKeyInteger));
        self::assertFalse($arrayKeyInteger->matches($arrayKeyString));

        self::assertTrue($arrayKeyString->matches($arrayKeyDefault));
        self::assertTrue($arrayKeyString->matches($arrayKeyString));
        self::assertFalse($arrayKeyString->matches($arrayKeyInteger));
    }

    public function test_matches_correct_union_types(): void
    {
        self::assertFalse(ArrayKeyType::default()->matches(new UnionType(NativeStringType::get(), NativeBooleanType::get())));
        self::assertTrue(ArrayKeyType::default()->matches(new UnionType(NativeStringType::get(), NativeIntegerType::get())));

        self::assertFalse(ArrayKeyType::string()->matches(new UnionType(NativeIntegerType::get(), NativeBooleanType::get())));
        self::assertTrue(ArrayKeyType::string()->matches(new UnionType(NativeStringType::get(), NativeIntegerType::get())));

        self::assertFalse(ArrayKeyType::integer()->matches(new UnionType(NativeStringType::get(), NativeBooleanType::get())));
        self::assertTrue(ArrayKeyType::integer()->matches(new UnionType(NativeStringType::get(), NativeIntegerType::get())));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse(ArrayKeyType::default()->matches(new FakeType()));
    }

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue(ArrayKeyType::default()->matches(new ScalarConcreteType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(ArrayKeyType::default()->matches(new MixedType()));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('int|string', ArrayKeyType::default()->nativeType()->toString());
        self::assertSame('int', ArrayKeyType::integer()->nativeType()->toString());
        self::assertSame('string', ArrayKeyType::string()->nativeType()->toString());
        self::assertSame('string|int', (new ArrayKeyType([
                new StringValueType('foo'),
                new IntegerValueType(42),
                new PositiveIntegerType(),
        ]))->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
