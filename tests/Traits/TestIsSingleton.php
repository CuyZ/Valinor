<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Traits;

use PHPUnit\Framework\TestCase;

use function str_replace;
use function substr;

/**
 * @mixin TestCase
 */
trait TestIsSingleton
{
    public function test_singleton_instance_can_be_fetched(): void
    {
        $class = substr(str_replace('Tests\\Unit\\', '', $this::class), 0, -4);

        $instanceA = $class::get();
        $instanceB = $class::get();

        self::assertSame($instanceA, $instanceB);
    }
}
