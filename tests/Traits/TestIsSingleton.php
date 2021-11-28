<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Traits;

use PHPUnit\Framework\TestCase;

use function get_class;
use function str_replace;
use function substr;

/**
 * @mixin TestCase
 */
trait TestIsSingleton
{
    /** @test */
    public function singleton_instance_can_be_fetched(): void
    {
        $class = substr(str_replace('Tests\\Unit\\', '', get_class($this)), 0, -4);

        $instanceA = $class::get();
        $instanceB = $class::get();

        self::assertSame($instanceA, $instanceB);
    }
}
