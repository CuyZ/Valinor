<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source\Modifier;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Modifier\PathMapping;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\Country;

final class PathMappingTest extends IntegrationTestCase
{
    public function test_path_with_sub_paths_are_mapped(): void
    {
        $source = new PathMapping([
            [
                'identification' => 'France',
                'towns' => [
                    [
                        'label' => 'Paris',
                        'timeZone' => 'Europe/Paris',
                    ],
                    [
                        'label' => 'Lyon',
                        'timeZone' => 'Europe/Paris',
                    ],
                ],
            ], [
                'identification' => 'Germany',
                'towns' => [
                    [
                        'label' => 'Berlin',
                        'timeZone' => 'Europe/Berlin',
                    ],
                    [
                        'label' => 'Hamburg',
                        'timeZone' => 'Europe/Berlin',
                    ],
                ],
            ],
        ], [
            '*.identification' => 'name',
            '*.towns' => 'cities',
            '*.towns.*.label' => 'name',
        ]);

        try {
            $countries = $this->mapperBuilder()->mapper()->map('list<' . Country::class . '>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('France', $countries[0]->name);
        self::assertSame('Paris', $countries[0]->cities[0]->name);
        self::assertSame('Lyon', $countries[0]->cities[1]->name);

        self::assertSame('Germany', $countries[1]->name);
        self::assertSame('Berlin', $countries[1]->cities[0]->name);
        self::assertSame('Hamburg', $countries[1]->cities[1]->name);
    }
}
