<?php

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\Error;

final class ClassNameCollisionTestCollision extends IntegrationTest
{
    public function test_mapping_to_class_with_same_class_name_as_native_class_works_properly(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(ObjectWithErrorsClassNameCollision::class, ['foo', 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->errors[0]->message);
        self::assertSame('bar', $result->errors[1]->message);
    }
}

final class ObjectWithErrorsClassNameCollision
{
    public function __construct(
        /** @var list<Error> */
        public array $errors
    ) {}
}
