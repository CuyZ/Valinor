<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\TypeResolution;

use A\B\Foo;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ImportedClassNameMatchingRootNamespaceMappingTest extends IntegrationTestCase
{
    public function test_imported_class_name_matching_root_namespace_does_not_block_type_resolution(): void
    {
        require_once 'imported-class-name-matching-root-namespace.php';

        try {
            $result = $this->mapperBuilder()->mapper()->map(Foo::class, [
                'foo' => ['bar' => 'bar!'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('bar!', $result->foo->bar);
    }
}
