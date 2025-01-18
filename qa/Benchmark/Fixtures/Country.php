<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\Benchmark\Fixtures;

final class Country
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,

        /** @var list<City> */
        public readonly array $cities,
    ) {}
}
