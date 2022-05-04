<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\TrueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TrueTypeTest extends TestCase
{
    use TestIsSingleton;

    private TrueType $trueType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trueType = new TrueType();
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('true', (string)$this->trueType);
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->trueType->accepts(true));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->trueType->accepts('Schwifty!'));
        self::assertFalse($this->trueType->accepts(42.1337));
        self::assertFalse($this->trueType->accepts(404));
        self::assertFalse($this->trueType->accepts(['foo' => 'bar']));
        self::assertFalse($this->trueType->accepts(false));
        self::assertFalse($this->trueType->accepts(null));
        self::assertFalse($this->trueType->accepts(new stdClass()));
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new TrueType())->matches(new TrueType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->trueType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new TrueType())->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_null_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new TrueType(),
            new FakeType(),
        );

        self::assertTrue($this->trueType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_null_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->trueType->matches($unionType));
    }
}
