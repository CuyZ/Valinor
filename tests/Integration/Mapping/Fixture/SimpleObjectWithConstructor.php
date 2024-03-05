<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

final class SimpleObjectWithConstructor
{
    public function __construct(public readonly string $value)
    {
    }
}
