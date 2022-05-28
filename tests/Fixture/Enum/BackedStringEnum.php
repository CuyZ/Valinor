<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Enum;

enum BackedStringEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}
