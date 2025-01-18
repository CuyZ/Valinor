<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\Benchmark;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Fixtures\Country;
use PhpBench\Attributes\Subject;

final class MapperBench
{
    #[Subject]
    public function initialisationBuilder(): void
    {
        $builder = new MapperBuilder();
    }

    #[Subject]
    public function initialisationMapper(): void
    {
        $builder = (new MapperBuilder())->mapper();
    }

    #[Subject]
    public function string(): void
    {
        $mapper = (new MapperBuilder())->mapper();

        $country = $mapper->map(
            'string',
            'foo',
        );
    }

    #[Subject]
    public function stringArray(): void
    {
        $mapper = (new MapperBuilder())->mapper();

        $json = <<<JSON
            [
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo",
                "foo"
            ]
        JSON;

        $country = $mapper->map(
            'list<string>',
            Source::json($json),
        );
    }

    #[Subject]
    public function readme(): void
    {
        $mapper = (new MapperBuilder())->mapper();
        $json = <<<JSON
            {
                "name": "France",
                "cities": [
                    {"name": "Paris", "timeZone": "Europe/Paris"},
                    {"name": "Lyon", "timeZone": "Europe/Paris"}
                ]
            }
        JSON;

        $country = $mapper->map(
            Country::class,
            Source::json($json),
        );
    }
}
