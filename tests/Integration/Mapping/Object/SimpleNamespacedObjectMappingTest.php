<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use SimpleNamespace\SimpleNamespacedObject;

final class SimpleNamespacedObjectMappingTest extends IntegrationTest
{
    public function test_simple_namespaced_object_can_be_mapped(): void
    {
        require_once(__DIR__ . '/../Fixture/SimpleNamespacedObject.php');

        try {
            $object = (new MapperBuilder())->mapper()->map(SimpleNamespacedObject::class, ['foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo'], $object->value);
    }
}
