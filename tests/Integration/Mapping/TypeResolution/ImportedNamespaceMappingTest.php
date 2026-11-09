<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\TypeResolution;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

final class ImportedNamespaceMappingTest extends IntegrationTestCase
{
    public function test_class_string_using_imported_namespace_can_be_mapped(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map(ObjectWithImportedNamespaceClassString::class, [
                'className' => Fixture\SimpleObject::class,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(Fixture\SimpleObject::class, $result->className);
    }
}

final readonly class ObjectWithImportedNamespaceClassString
{
    /**
     * @param class-string<Fixture\SimpleObject> $className
     */
    public function __construct(public string $className) {}
}
