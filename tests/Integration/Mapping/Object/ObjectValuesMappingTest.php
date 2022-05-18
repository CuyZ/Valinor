<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

final class ObjectValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'string' => 'foo',
            'object' => [
                'value' => 'foo',
            ],
        ];

        foreach ([ObjectValues::class, ObjectValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame('foo', $result->object->value);
        }
    }

    public function test_invalid_iterable_source_throws_exception(): void
    {
        $source = 'foo';

        foreach ([ObjectValues::class, ObjectValuesWithConstructor::class] as $class) {
            try {
                $this->mapperBuilder->mapper()->map($class, $source);
            } catch (MappingError $exception) {
                $error = $exception->node()->messages()[0];

                self::assertSame('1632903281', $error->code());
                self::assertSame("Value 'foo' does not match type `array{object: ?, string: string}`.", (string)$error);
            }
        }
    }
}

class ObjectValues
{
    public SimpleObject $object;

    public string $string;
}

class ObjectValuesWithConstructor extends ObjectValues
{
    public function __construct(SimpleObject $object, string $string)
    {
        $this->object = $object;
        $this->string = $string;
    }
}
