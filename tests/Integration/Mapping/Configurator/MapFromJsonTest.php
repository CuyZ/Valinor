<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapFromJson;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class MapFromJsonTest extends IntegrationTestCase
{
    public function test_attribute_maps_json_string_to_list(): void
    {
        $class = new class () {
            /** @var list<string> */
            #[MapFromJson]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '["admin", "editor"]');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['admin', 'editor'], $result->value);
    }

    public function test_attribute_maps_json_string_to_list_on_promoted_property(): void
    {
        $class = new class ([]) {
            /**
             * @param list<string> $value
             */
            public function __construct(
                #[MapFromJson]
                public array $value,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '["admin", "editor"]');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['admin', 'editor'], $result->value);
    }

    public function test_attribute_maps_json_object_to_shaped_array(): void
    {
        $class = new class () {
            /** @var array{name: string, age: int} */
            #[MapFromJson]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '{"name": "John Doe", "age": 42}');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['name' => 'John Doe', 'age' => 42], $result->value);
    }

    public function test_invalid_json_raises_mapping_error(): void
    {
        $class = new class () {
            public string $name;

            /** @var list<string> */
            #[MapFromJson]
            public array $roles;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['name' => 'John Doe', 'roles' => 'not json']);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            $message = $error->messages()->toArray()[0];

            self::assertSame('roles', $message->path());
            self::assertSame('invalid_json', $message->code());
        }
    }
}
