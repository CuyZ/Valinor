<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake;

use Reflector;

final class FakeReflector implements Reflector
{
    public static function export(): ?string
    {
        return 'fake';
    }

    public function __toString(): string
    {
        return 'fake';
    }
}
