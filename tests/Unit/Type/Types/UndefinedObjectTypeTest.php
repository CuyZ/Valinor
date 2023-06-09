<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class UndefinedObjectTypeTest extends TestCase
{
    use TestIsSingleton;

    private UndefinedObjectType $undefinedObjectType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->undefinedObjectType = new UndefinedObjectType();
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('object', $this->undefinedObjectType->toString());
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->undefinedObjectType->accepts(new stdClass()));
        self::assertTrue($this->undefinedObjectType->accepts(new class () {}));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->undefinedObjectType->accepts(null));
        self::assertFalse($this->undefinedObjectType->accepts('Schwifty!'));
        self::assertFalse($this->undefinedObjectType->accepts(42.1337));
        self::assertFalse($this->undefinedObjectType->accepts(404));
        self::assertFalse($this->undefinedObjectType->accepts(['foo' => 'bar']));
        self::assertFalse($this->undefinedObjectType->accepts(false));
    }

    public function test_matches_valid_types(): void
    {
        self::assertTrue($this->undefinedObjectType->matches(new UndefinedObjectType()));
        self::assertTrue($this->undefinedObjectType->matches(new FakeObjectType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->undefinedObjectType->matches(new FakeType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new FakeObjectType(),
            new FakeType(),
        );

        self::assertTrue($this->undefinedObjectType->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->undefinedObjectType->matches($unionType));
    }

    public function test_matches_intersection_type(): void
    {
        self::assertTrue($this->undefinedObjectType->matches(new IntersectionType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->undefinedObjectType->matches(new MixedType()));
    }
}
