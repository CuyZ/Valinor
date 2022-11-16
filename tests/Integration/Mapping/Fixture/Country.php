<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

final class Country
{
    public string $name;

    /** @var list<City> */
    public array $cities;
}
