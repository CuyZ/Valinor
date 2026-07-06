<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class NativeIntegerTypeTest extends UnitTestCase
{
    use TestIsSingleton;

    private NativeIntegerType $integerType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integerType = new NativeIntegerType();
    }

    #[TestWith([404])]
    #[TestWith([-404])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->integerType->accepts($value));
        self::assertTrue($this->compiledAccept($this->integerType, $value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([-42.1337])]
    #[TestWith([42.1337])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->integerType->accepts($value));
        self::assertFalse($this->compiledAccept($this->integerType, $value));
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('int', $this->integerType->toString());
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new NativeIntegerType())->matches(new NativeIntegerType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->integerType->matches(new FakeType()));
    }

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue($this->integerType->matches(new ScalarConcreteType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->integerType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_integer_type(): void
    {
        $union = new UnionType(new FakeType(), new NativeIntegerType(), new FakeType());

        self::assertTrue($this->integerType->matches($union));
    }

    public function test_does_not_match_union_type_not_containing_integer_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->integerType->matches($unionType));
    }

    public function test_matches_default_array_key_type(): void
    {
        self::assertTrue($this->integerType->matches(ArrayKeyType::default()));
    }

    public function test_matches_array_key_type_with_integer_type(): void
    {
        self::assertTrue($this->integerType->matches(ArrayKeyType::integer()));
    }

    public function test_does_not_match_array_key_type_with_string_type(): void
    {
        self::assertFalse($this->integerType->matches(ArrayKeyType::string()));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('int', (new NativeIntegerType())->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
