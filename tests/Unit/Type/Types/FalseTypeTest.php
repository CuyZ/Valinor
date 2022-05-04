<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\FalseType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FalseTypeTest extends TestCase
{
    use TestIsSingleton;

    private FalseType $falseType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->falseType = new FalseType();
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('false', (string)$this->falseType);
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->falseType->accepts(false));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->falseType->accepts('Schwifty!'));
        self::assertFalse($this->falseType->accepts(42.1337));
        self::assertFalse($this->falseType->accepts(404));
        self::assertFalse($this->falseType->accepts(['foo' => 'bar']));
        self::assertFalse($this->falseType->accepts(true));
        self::assertFalse($this->falseType->accepts(null));
        self::assertFalse($this->falseType->accepts(new stdClass()));
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new FalseType())->matches(new FalseType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->falseType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new FalseType())->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_null_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new FalseType(),
            new FakeType(),
        );

        self::assertTrue($this->falseType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_null_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->falseType->matches($unionType));
    }
}
