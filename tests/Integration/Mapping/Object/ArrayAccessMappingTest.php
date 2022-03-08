<?php

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

class ArrayAccessMappingTest extends IntegrationTest
{
    public function test_array_access_classes_are_handled_successfully(): void
    {
        try {
            $result = $this->mapperBuilder->mapper()->map(ObjectContainingAnArrayAccess::class, [
                'property' => [
                    'foo' => 'bar',
                ],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(ObjectContainingAnArrayAccess::class, $result);
        self::assertSame('bar', $result->property['foo']);
    }
}

final class ObjectContainingAnArrayAccess
{
    public ArrayAccessSimple $property;
}

final class ArrayAccessSimple implements \ArrayAccess
{
    private array $container;

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->container);
    }

    public function offsetGet($offset)
    {
        return $this->container[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->container[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
}
