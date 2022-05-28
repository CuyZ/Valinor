<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Enum;

enum BackedIntegerEnum: int
{
    case FOO = 42;
    case BAR = 404;
    case BAZ = 1337;
}
