<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @requires PHP >= 8.1
 */
final class EnumTypeTest extends TestCase
{
    private EnumType $enumType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enumType = new EnumType(PureEnum::class);
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->enumType->accepts(PureEnum::FOO));
        self::assertTrue($this->enumType->accepts(PureEnum::BAR));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->enumType->accepts(null));
        self::assertFalse($this->enumType->accepts('Schwifty!'));
        self::assertFalse($this->enumType->accepts(42.1337));
        self::assertFalse($this->enumType->accepts(404));
        self::assertFalse($this->enumType->accepts(['foo' => 'bar']));
        self::assertFalse($this->enumType->accepts(false));
        self::assertFalse($this->enumType->accepts(new stdClass()));
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame(PureEnum::class, (string)$this->enumType);
    }

    public function test_matches_same_enum_type(): void
    {
        $enumTypeA = new EnumType(PureEnum::class);
        $enumTypeB = new EnumType(PureEnum::class);

        self::assertTrue($enumTypeA->matches($enumTypeB));
    }

    public function test_does_not_match_other_enum_type(): void
    {
        $enumTypeA = new EnumType(PureEnum::class);
        $enumTypeB = new EnumType(BackedStringEnum::class);

        self::assertFalse($enumTypeA->matches($enumTypeB));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->enumType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->enumType->matches(new MixedType()));
    }

    public function test_matches_undefined_object_type(): void
    {
        self::assertTrue($this->enumType->matches(new UndefinedObjectType()));
    }

    public function test_matches_union_type_containing_float_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new EnumType(PureEnum::class),
            new FakeType(),
        );

        self::assertTrue($this->enumType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_float_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->enumType->matches($unionType));
    }
}
