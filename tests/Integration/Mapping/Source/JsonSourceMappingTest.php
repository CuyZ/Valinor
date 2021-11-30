<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

final class JsonSourceMappingTest extends IntegrationTest
{
    public function test_json_source_is_mapped_correctly(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SimpleObject::class,
                new JsonSource('{"value": "foo"}')
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->value);
    }
}
