<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use SimpleNamespace\SimpleNamespacedObject;

final class SimpleNamespacedObjectMappingTest extends IntegrationTestCase
{
    public function test_simple_namespaced_object_can_be_mapped(): void
    {
        require_once(__DIR__ . '/../Fixture/SimpleNamespacedObject.php');

        try {
            $object = $this->mapperBuilder()->mapper()->map(SimpleNamespacedObject::class, ['foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo'], $object->value);
    }
}
