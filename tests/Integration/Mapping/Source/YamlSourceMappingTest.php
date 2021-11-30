<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\YamlSource;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

/**
 * @requires extension yaml
 */
final class YamlSourceMappingTest extends IntegrationTest
{
    public function test_yaml_source_is_mapped_correctly(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SimpleObject::class,
                new YamlSource('value: foo')
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->value);
    }
}
