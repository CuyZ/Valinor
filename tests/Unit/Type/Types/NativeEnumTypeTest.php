<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeEnumType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @requires PHP >= 8.1
 */
final class NativeEnumTypeTest extends TestCase
{
    private NativeEnumType $pureEnumType;

    private NativeEnumType $backedStringEnumType;

    private NativeEnumType $backedIntegerEnumType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pureEnumType = new NativeEnumType(PureEnum::class);
        $this->backedStringEnumType = new NativeEnumType(BackedStringEnum::class);
        $this->backedIntegerEnumType = new NativeEnumType(BackedIntegerEnum::class);
    }

    public function test_class_name_can_be_retrieved(): void
    {
        self::assertSame(PureEnum::class, $this->pureEnumType->className());
        self::assertSame(BackedStringEnum::class, $this->backedStringEnumType->className());
        self::assertSame(BackedIntegerEnum::class, $this->backedIntegerEnumType->className());
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->pureEnumType->accepts(PureEnum::FOO));
        self::assertTrue($this->backedStringEnumType->accepts(BackedStringEnum::FOO));
        self::assertTrue($this->backedIntegerEnumType->accepts(BackedIntegerEnum::FOO));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->pureEnumType->accepts(null));
        self::assertFalse($this->pureEnumType->accepts('Schwifty!'));
        self::assertFalse($this->pureEnumType->accepts(42.1337));
        self::assertFalse($this->pureEnumType->accepts(404));
        self::assertFalse($this->pureEnumType->accepts(['foo' => 'bar']));
        self::assertFalse($this->pureEnumType->accepts(false));
        self::assertFalse($this->pureEnumType->accepts(new stdClass()));
        self::assertFalse($this->pureEnumType->accepts(BackedIntegerEnum::FOO));

        self::assertFalse($this->backedStringEnumType->accepts(null));
        self::assertFalse($this->backedStringEnumType->accepts('Schwifty!'));
        self::assertFalse($this->backedStringEnumType->accepts(42.1337));
        self::assertFalse($this->backedStringEnumType->accepts(404));
        self::assertFalse($this->backedStringEnumType->accepts(['foo' => 'bar']));
        self::assertFalse($this->backedStringEnumType->accepts(false));
        self::assertFalse($this->backedStringEnumType->accepts(new stdClass()));
        self::assertFalse($this->backedStringEnumType->accepts(PureEnum::FOO));

        self::assertFalse($this->backedIntegerEnumType->accepts(null));
        self::assertFalse($this->backedIntegerEnumType->accepts('Schwifty!'));
        self::assertFalse($this->backedIntegerEnumType->accepts(42.1337));
        self::assertFalse($this->backedIntegerEnumType->accepts(404));
        self::assertFalse($this->backedIntegerEnumType->accepts(['foo' => 'bar']));
        self::assertFalse($this->backedIntegerEnumType->accepts(false));
        self::assertFalse($this->backedIntegerEnumType->accepts(new stdClass()));
        self::assertFalse($this->backedIntegerEnumType->accepts(BackedStringEnum::FOO));
    }

    public function test_can_cast_enum_value(): void
    {
        self::assertTrue($this->pureEnumType->canCast('FOO'));
        self::assertTrue($this->pureEnumType->canCast(new StringableObject('FOO')));

        self::assertTrue($this->backedStringEnumType->canCast('foo'));
        self::assertTrue($this->backedStringEnumType->canCast(new StringableObject('foo')));

        self::assertTrue($this->backedIntegerEnumType->canCast(42));
        self::assertTrue($this->backedIntegerEnumType->canCast('42'));
        self::assertTrue($this->backedIntegerEnumType->canCast(new StringableObject('42')));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->pureEnumType->canCast(404));
        self::assertFalse($this->pureEnumType->canCast(42.1337));
        self::assertFalse($this->pureEnumType->canCast(null));
        self::assertFalse($this->pureEnumType->canCast(['foo' => 'bar']));
        self::assertFalse($this->pureEnumType->canCast('Schwifty!'));
        self::assertFalse($this->pureEnumType->canCast(new StringableObject('Schwifty!')));
        self::assertFalse($this->pureEnumType->canCast(false));
        self::assertFalse($this->pureEnumType->canCast(new stdClass()));

        self::assertFalse($this->backedStringEnumType->canCast(404));
        self::assertFalse($this->backedStringEnumType->canCast(42.1337));
        self::assertFalse($this->backedStringEnumType->canCast(null));
        self::assertFalse($this->backedStringEnumType->canCast(['foo' => 'bar']));
        self::assertFalse($this->backedStringEnumType->canCast('Schwifty!'));
        self::assertFalse($this->backedStringEnumType->canCast(new StringableObject('Schwifty!')));
        self::assertFalse($this->backedStringEnumType->canCast(false));
        self::assertFalse($this->backedStringEnumType->canCast(new stdClass()));

        self::assertFalse($this->backedIntegerEnumType->canCast(512));
        self::assertFalse($this->backedIntegerEnumType->canCast(42.1337));
        self::assertFalse($this->backedIntegerEnumType->canCast(null));
        self::assertFalse($this->backedIntegerEnumType->canCast(['foo' => 'bar']));
        self::assertFalse($this->backedIntegerEnumType->canCast('Schwifty!'));
        self::assertFalse($this->backedIntegerEnumType->canCast(new StringableObject('Schwifty!')));
        self::assertFalse($this->backedIntegerEnumType->canCast(false));
        self::assertFalse($this->backedIntegerEnumType->canCast(new stdClass()));
    }

    public function test_cast_value_returns_correct_result(): void
    {
        self::assertSame(PureEnum::FOO, $this->pureEnumType->cast('FOO'));

        self::assertSame(BackedStringEnum::FOO, $this->backedStringEnumType->cast('foo'));

        self::assertSame(BackedIntegerEnum::FOO, $this->backedIntegerEnumType->cast(42));
        self::assertSame(BackedIntegerEnum::FOO, $this->backedIntegerEnumType->cast('42'));
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->pureEnumType->cast('Schwifty!');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame(PureEnum::class, $this->pureEnumType->toString());
        self::assertSame(BackedStringEnum::class, $this->backedStringEnumType->toString());
        self::assertSame(BackedIntegerEnum::class, $this->backedIntegerEnumType->toString());
    }

    public function test_matches_same_enum_type(): void
    {
        self::assertTrue($this->pureEnumType->matches(new NativeEnumType(PureEnum::class)));
        self::assertTrue($this->backedStringEnumType->matches(new NativeEnumType(BackedStringEnum::class)));
        self::assertTrue($this->backedIntegerEnumType->matches(new NativeEnumType(BackedIntegerEnum::class)));
    }

    public function test_does_not_match_other_enum_type(): void
    {
        self::assertFalse($this->pureEnumType->matches(new NativeEnumType(BackedStringEnum::class)));
        self::assertFalse($this->backedStringEnumType->matches(new NativeEnumType(BackedIntegerEnum::class)));
        self::assertFalse($this->backedIntegerEnumType->matches(new NativeEnumType(PureEnum::class)));
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
        self::assertTrue($this->pureEnumType->matches(new UnionType(new FakeType(), new NativeEnumType(PureEnum::class))));
        self::assertTrue($this->backedStringEnumType->matches(new UnionType(new FakeType(), new NativeEnumType(BackedStringEnum::class))));
        self::assertTrue($this->backedIntegerEnumType->matches(new UnionType(new FakeType(), new NativeEnumType(BackedIntegerEnum::class))));
    }

    public function test_does_not_match_union_type_not_containing_same_enum_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->pureEnumType->matches($unionType));
        self::assertFalse($this->backedStringEnumType->matches($unionType));
        self::assertFalse($this->backedIntegerEnumType->matches($unionType));
    }
}
