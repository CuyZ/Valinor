<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\MixedType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MixedTypeTest extends TestCase
{
    use TestIsSingleton;

    private MixedType $mixedType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mixedType = new MixedType();
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('mixed', (string)$this->mixedType);
    }

    public function test_accepts_every_value(): void
    {
        self::assertTrue($this->mixedType->accepts(null));
        self::assertTrue($this->mixedType->accepts('Schwifty!'));
        self::assertTrue($this->mixedType->accepts(42.1337));
        self::assertTrue($this->mixedType->accepts(404));
        self::assertTrue($this->mixedType->accepts(['foo' => 'bar']));
        self::assertTrue($this->mixedType->accepts(false));
        self::assertTrue($this->mixedType->accepts(new stdClass()));
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new MixedType())->matches(new MixedType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->mixedType->matches(new FakeType()));
    }
}
