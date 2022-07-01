<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\TestCase;

final class UnresolvableTypeTest extends TestCase
{
    public function test_call_unresolvable_type_accepts_throws_itself(): void
    {
        $type = new UnresolvableType('some-type', 'some message');

        $this->expectExceptionObject($type);

        $type->accepts('foo');
    }

    public function test_call_unresolvable_type_matches_throws_itself(): void
    {
        $type = new UnresolvableType('some-type', 'some message');

        $this->expectExceptionObject($type);

        $type->matches(new FakeType());
    }

    public function test_cast_string_unresolvable_type_returns_type(): void
    {
        $type = new UnresolvableType('some-type', 'some message');

        self::assertSame('some-type', (string)$type);
    }
}
