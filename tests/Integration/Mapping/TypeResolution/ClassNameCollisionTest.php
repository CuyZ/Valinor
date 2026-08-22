<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\TypeResolution;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\Error;

final class ClassNameCollisionTest extends IntegrationTestCase
{
    public function test_mapping_to_class_with_same_class_name_as_native_class_works_properly(): void
    {
        try {
            $result = $this->mapperBuilder()
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
