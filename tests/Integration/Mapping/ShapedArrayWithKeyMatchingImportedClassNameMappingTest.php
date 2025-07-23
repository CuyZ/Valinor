<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping {

    use CuyZ\Valinor\Mapper\MappingError;
    use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
    use SomeNamespaceB\ClassWithShapedArrayWithKeyMatchingImportedClassName;

    final class ShapedArrayWithKeyMatchingImportedClassNameMappingTest extends IntegrationTestCase
    {
        public function test_shaped_array_with_key_matching_imported_class_name_can_be_mapped_properly(): void
        {
            try {
                $object = $this->mapperBuilder()->mapper()->map(ClassWithShapedArrayWithKeyMatchingImportedClassName::class, [
                    'Element' => 'Some element',
                ]);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame('Some element', $object->elements['Element']->value);
        }
    }
}

namespace SomeNamespaceA {

    final class Element
    {
        public function __construct(
            public string $value,
        ) {}
    }
}

namespace SomeNamespaceB {

    use SomeNamespaceA\Element;

    final class ClassWithShapedArrayWithKeyMatchingImportedClassName
    {
        public function __construct(
            /** @var array{Element: Element} */
            public array $elements,
        ) {}
    }
}
