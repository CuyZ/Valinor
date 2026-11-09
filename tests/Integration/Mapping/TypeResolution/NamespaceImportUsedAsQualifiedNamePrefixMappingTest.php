<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\TypeResolution;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use NamespaceImportUsedAsQualifiedNamePrefix\OtherNamespace\SomeObject;
use NamespaceImportUsedAsQualifiedNamePrefix\SomeNamespace\SubNamespace\SomeClass;

final class NamespaceImportUsedAsQualifiedNamePrefixMappingTest extends IntegrationTestCase
{
    /**
     * @see https://github.com/CuyZ/Valinor/issues/856
     */
    public function test_namespace_import_used_as_qualified_name_prefix_is_expanded(): void
    {
        require_once 'namespace-import-used-as-qualified-name-prefix.php';

        try {
            $result = $this->mapperBuilder()->mapper()->map(SomeObject::class, SomeClass::class);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(SomeClass::class, $result->className);
    }
}
