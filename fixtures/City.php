<?php
declare(strict_types = 1);

namespace CuyZ\Valinor\Fixtures;

use DateTimeZone;

final class City
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,

        public readonly DateTimeZone $timeZone,
    ) {}
}
