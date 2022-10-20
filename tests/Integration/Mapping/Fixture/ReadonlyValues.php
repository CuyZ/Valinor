<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

// PHP8.1 move inside \CuyZ\Valinor\Tests\Integration\Mapping\ReadonlyMappingTest
final class ReadonlyValues
{
    public readonly string $value; // @phpstan-ignore-line
}
