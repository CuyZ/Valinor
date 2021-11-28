<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class EmptyAttributesTest extends TestCase
{
    public function test_empty_attributes_returns_empty_results(): void
    {
        $attributes = EmptyAttributes::get();

        self::assertEmpty(iterator_to_array($attributes));
        self::assertCount(0, $attributes);
        self::assertFalse($attributes->has(BasicAttribute::class));
        self::assertEmpty($attributes->ofType(BasicAttribute::class));
    }
}
