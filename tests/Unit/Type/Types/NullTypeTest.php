<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NullTypeTest extends TestCase
{
    use TestIsSingleton;

    private NullType $nullType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nullType = new NullType();
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('null', (string)$this->nullType);
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->nullType->accepts(null));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->nullType->accepts('Schwifty!'));
        self::assertFalse($this->nullType->accepts(42.1337));
        self::assertFalse($this->nullType->accepts(404));
        self::assertFalse($this->nullType->accepts(['foo' => 'bar']));
        self::assertFalse($this->nullType->accepts(false));
        self::assertFalse($this->nullType->accepts(new stdClass()));
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new NullType())->matches(new NullType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->nullType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new NullType())->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_null_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NullType(),
            new FakeType(),
        );

        self::assertTrue($this->nullType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_null_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->nullType->matches($unionType));
    }
}
