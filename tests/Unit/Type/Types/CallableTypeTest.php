<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\CallableType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CallableTypeTest extends TestCase
{
    use TestIsSingleton;

    private CallableType $callableType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->callableType = new CallableType();
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->callableType->accepts(fn () => null));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->callableType->accepts(true));
        self::assertFalse($this->callableType->accepts(null));
        self::assertFalse($this->callableType->accepts('Schwifty!'));
        self::assertFalse($this->callableType->accepts(42.1337));
        self::assertFalse($this->callableType->accepts(404));
        self::assertFalse($this->callableType->accepts(['foo' => 'bar']));
        self::assertFalse($this->callableType->accepts(new stdClass()));
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('callable', $this->callableType->toString());
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new CallableType())->matches(new CallableType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->callableType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->callableType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_callable_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new CallableType(),
            new FakeType(),
        );

        self::assertTrue($this->callableType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_boolean_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->callableType->matches($unionType));
    }
}
