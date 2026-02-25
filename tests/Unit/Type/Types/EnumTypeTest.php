<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

final class EnumTypeTest extends UnitTestCase
{
    private EnumType $pureEnumType;

    private EnumType $backedStringEnumType;

    private EnumType $backedIntegerEnumType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pureEnumType = EnumType::native(PureEnum::class);
        $this->backedStringEnumType = EnumType::native(BackedStringEnum::class);
        $this->backedIntegerEnumType = EnumType::native(BackedIntegerEnum::class);
    }

    public function test_class_name_can_be_retrieved(): void
    {
        self::assertSame(PureEnum::class, $this->pureEnumType->className());
        self::assertSame(BackedStringEnum::class, $this->backedStringEnumType->className());
        self::assertSame(BackedIntegerEnum::class, $this->backedIntegerEnumType->className());
    }

    #[TestWith(['accepts' => true, 'value' => PureEnum::FOO])]
    #[TestWith(['accepts' => false, 'value' => BackedStringEnum::FOO])]
    #[TestWith(['accepts' => false, 'value' => BackedIntegerEnum::FOO])]
    public function test_pure_enum_accepts_correct_values(bool $accepts, mixed $value): void
    {
        self::assertSame($accepts, $this->pureEnumType->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($this->pureEnumType, $value));
    }

    #[TestWith(['accepts' => true, 'value' => BackedStringEnum::FOO])]
    #[TestWith(['accepts' => false, 'value' => PureEnum::FOO])]
    #[TestWith(['accepts' => false, 'value' => BackedIntegerEnum::FOO])]
    public function test_backed_string_enum_accepts_correct_values(bool $accepts, mixed $value): void
    {
        self::assertSame($accepts, $this->backedStringEnumType->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($this->backedStringEnumType, $value));
    }

    #[TestWith(['accepts' => true, 'value' => BackedIntegerEnum::FOO])]
    #[TestWith(['accepts' => false, 'value' => PureEnum::FOO])]
    #[TestWith(['accepts' => false, 'value' => BackedStringEnum::FOO])]
    public function test_backed_integer_enum_accepts_correct_values(bool $accepts, mixed $value): void
    {
        self::assertSame($accepts, $this->backedIntegerEnumType->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($this->backedIntegerEnumType, $value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->pureEnumType->accepts($value));
        self::assertFalse($this->backedStringEnumType->accepts($value));
        self::assertFalse($this->backedIntegerEnumType->accepts($value));

        self::assertFalse($this->compiledAccept($this->pureEnumType, $value));
        self::assertFalse($this->compiledAccept($this->backedStringEnumType, $value));
        self::assertFalse($this->compiledAccept($this->backedIntegerEnumType, $value));
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame(PureEnum::class, $this->pureEnumType->toString());
        self::assertSame(BackedStringEnum::class, $this->backedStringEnumType->toString());
        self::assertSame(BackedIntegerEnum::class, $this->backedIntegerEnumType->toString());
    }

    public function test_matches_same_enum_type(): void
    {
        self::assertTrue($this->pureEnumType->matches(EnumType::native(PureEnum::class)));
        self::assertTrue($this->backedStringEnumType->matches(EnumType::native(BackedStringEnum::class)));
        self::assertTrue($this->backedIntegerEnumType->matches(EnumType::native(BackedIntegerEnum::class)));
    }

    public function test_does_not_match_other_enum_type(): void
    {
        self::assertFalse($this->pureEnumType->matches(EnumType::native(BackedStringEnum::class)));
        self::assertFalse($this->backedStringEnumType->matches(EnumType::native(BackedIntegerEnum::class)));
        self::assertFalse($this->backedIntegerEnumType->matches(EnumType::native(PureEnum::class)));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->pureEnumType->matches(new FakeType()));
        self::assertFalse($this->backedStringEnumType->matches(new FakeType()));
        self::assertFalse($this->backedIntegerEnumType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->pureEnumType->matches(new MixedType()));
        self::assertTrue($this->backedStringEnumType->matches(new MixedType()));
        self::assertTrue($this->backedIntegerEnumType->matches(new MixedType()));
    }

    public function test_matches_undefined_object_type(): void
    {
        self::assertTrue($this->pureEnumType->matches(new UndefinedObjectType()));
        self::assertTrue($this->backedStringEnumType->matches(new UndefinedObjectType()));
        self::assertTrue($this->backedIntegerEnumType->matches(new UndefinedObjectType()));
    }

    public function test_matches_union_type_containing_same_enum_type(): void
    {
        self::assertTrue($this->pureEnumType->matches(new UnionType(new FakeType(), EnumType::native(PureEnum::class))));
        self::assertTrue($this->backedStringEnumType->matches(new UnionType(new FakeType(), EnumType::native(BackedStringEnum::class))));
        self::assertTrue($this->backedIntegerEnumType->matches(new UnionType(new FakeType(), EnumType::native(BackedIntegerEnum::class))));
    }

    public function test_does_not_match_union_type_not_containing_same_enum_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->pureEnumType->matches($unionType));
        self::assertFalse($this->backedStringEnumType->matches($unionType));
        self::assertFalse($this->backedIntegerEnumType->matches($unionType));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame(PureEnum::class, $this->pureEnumType->nativeType()->toString());
        self::assertSame(BackedStringEnum::class, $this->backedStringEnumType->nativeType()->toString());
        self::assertSame(BackedIntegerEnum::class, $this->backedIntegerEnumType->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
