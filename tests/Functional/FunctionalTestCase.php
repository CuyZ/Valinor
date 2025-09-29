<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional;

use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use PHPUnit\Framework\TestCase;

abstract class FunctionalTestCase extends TestCase
{
    /**
     * @template T of object
     * @param class-string<T> $name
     * @return T
     */
    protected function getService(string $name, Settings $settings = new Settings()): object
    {
        return (new Container($settings))->get($name);
    }
}
