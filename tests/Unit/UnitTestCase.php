<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit;

use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use PHPUnit\Framework\TestCase;

abstract class UnitTestCase extends TestCase
{
    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    protected function getService(string $className, Settings $settings = new Settings()): object
    {
        return (new Container($settings))->get($className);
    }
}
