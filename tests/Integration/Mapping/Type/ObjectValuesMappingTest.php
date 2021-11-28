<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Type;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

final class ObjectValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
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

//    public function test_empty_mandatory_value_throws_exception(): void
//    {
//        try {
//            $this->mapperBuilder->mapper()->map(ObjectValues::class, [
//                'object' => false,
//            ]);
//        } catch (MappingError $exception) {
//            ray($exception->describe());
//            $error = $exception->describe()['object'][0];
//            var_dump($error->getMessage());
//
////            self::assertInstanceOf(CannotCastToScalarValue::class, $error);
////            self::assertSame(1618736242, $error->getCode());
////            self::assertSame('Cannot be empty and must be filled with a value of type `string`.', $error->getMessage());
////
////            throw $exception;
//        }
//    }
}

class ObjectValues
{
    public SimpleObject $object;
}

class ObjectValuesWithConstructor extends ObjectValues
{
    public function __construct(SimpleObject $object)
    {
        $this->object = $object;
    }
}
