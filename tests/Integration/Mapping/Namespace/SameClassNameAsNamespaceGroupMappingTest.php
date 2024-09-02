<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Namespace;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use SomeNamespace\SomeClass;
use SomeNamespace\SomeNamespace;

final class SameClassNameAsNamespaceGroupMappingTest extends IntegrationTestCase
{
    // @see https://github.com/CuyZ/Valinor/issues/554
    public function test_class_name_has_same_name_as_namespace_group_dot_not_block_type_resolution(): void
    {
        require_once 'class-name-with-same-name-as-namespace-group.php';

        try {
            $result = $this->mapperBuilder()->mapper()->map(ClassContainingNamespacedClasses::class, [
                'objectA' => ['stringValue' => 'foo'],
                'objectB' => ['integerValue' => 42],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->objectA->stringValue);
        self::assertSame(42, $result->objectB->integerValue);
    }
}

final class ClassContainingNamespacedClasses
{
    public SomeNamespace $objectA;
    public SomeClass $objectB;
}
