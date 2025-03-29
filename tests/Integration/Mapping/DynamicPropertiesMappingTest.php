<?php

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class DynamicPropertiesMappingTest extends IntegrationTestCase
{
    public function test_map_dynamic_properties_to_class(): void
    {
        $class = new class () implements \ArrayAccess {
            /** @var array<string, string> */
            public array $storage = [];

            public function __get(string $name): mixed
            {
                return $this->offsetGet($name);
            }

            public function __set(string $name, mixed $value): void
            {
                $this->offsetSet($name, $value);
            }

            public function __isset(string $name): bool
            {
                return $this->offsetExists($name);
            }

            public function offsetExists(mixed $offset): bool
            {
                return isset($this->storage[$offset]);
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $this->storage[$offset] ?? null;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                if (!is_string($offset)) {
                    return;
                }
                $this->storage[$offset] = is_string($value) ? $value : serialize($value);
            }

            public function offsetUnset(mixed $offset): void
            {
                unset($this->storage[$offset]);
            }
        };

        $res = $this->mapperBuilder()
            ->allowDynamicPropertiesFor(
                \ArrayAccess::class,
            )
            ->mapper()
            ->map($class::class, [
                'bar' => 'baz',
                'storage' => ['foo' => 'bar'],
            ]);

        self::assertSame('bar', $res['foo']);
        self::assertSame('baz', $res['bar']);
    }
}
