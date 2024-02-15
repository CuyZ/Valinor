<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\YamlSource;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('yaml')]
final class YamlSourceMappingTest extends IntegrationTestCase
{
    public function test_yaml_source_is_mapped_correctly(): void
    {
        $class = new class () {
            public string $foo;

            public string $bar;
        };

        try {
            $object = $this->mapperBuilder()->mapper()->map(
                $class::class,
                new YamlSource("foo: foo\nbar: bar")
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->foo);
        self::assertSame('bar', $object->bar);
    }
}
