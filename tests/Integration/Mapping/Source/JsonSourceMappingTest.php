<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class JsonSourceMappingTest extends IntegrationTestCase
{
    public function test_json_source_is_mapped_correctly(): void
    {
        $class = new class () {
            public string $foo;

            public string $bar;
        };

        try {
            $object = $this->mapperBuilder()->mapper()->map(
                $class::class,
                new JsonSource('{"foo": "foo", "bar": "bar"}')
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->foo);
        self::assertSame('bar', $object->bar);
    }
}
