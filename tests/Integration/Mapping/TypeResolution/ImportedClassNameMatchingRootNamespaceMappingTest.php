<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\TypeResolution;

use A\B\Bar;
use A\B\ChildBar;
use A\B\Foo;
use A\B\FooWithDocBlockSelf;
use A\B\FooWithSelf;
use A\B\FooWithStaticConstructor;
use A\B\FooWithUnion;
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

    public function test_imported_class_name_matching_root_namespace_does_not_block_union_type_resolution(): void
    {
        require_once 'imported-class-name-matching-root-namespace.php';

        try {
            $result = $this->mapperBuilder()->mapper()->map(FooWithUnion::class, [
                'foo' => ['bar' => 'bar!'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(Bar::class, $result->foo);
        self::assertSame('bar!', $result->foo->bar);
    }

    public function test_imported_class_name_matching_root_namespace_does_not_block_self_type_resolution(): void
    {
        require_once 'imported-class-name-matching-root-namespace.php';

        try {
            $result = $this->mapperBuilder()->mapper()->map(FooWithSelf::class, [
                'bar' => 'bar!',
                'child' => ['bar' => 'child!'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('child!', $result->child?->bar);
    }

    public function test_imported_class_name_matching_root_namespace_does_not_block_static_return_type_resolution(): void
    {
        require_once 'imported-class-name-matching-root-namespace.php';

        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(FooWithStaticConstructor::create(...))
                ->mapper()
                ->map(FooWithStaticConstructor::class, 'bar!');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('bar!', $result->bar);
    }

    public function test_imported_class_name_matching_root_namespace_does_not_block_docblock_self_type_resolution(): void
    {
        require_once 'imported-class-name-matching-root-namespace.php';

        try {
            $result = $this->mapperBuilder()->mapper()->map(FooWithDocBlockSelf::class, [
                'bar' => 'bar!',
                'children' => [['bar' => 'child!', 'children' => []]],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('child!', $result->children[0]->bar);
    }

    public function test_imported_class_name_matching_root_namespace_does_not_block_parent_class_resolution(): void
    {
        require_once 'imported-class-name-matching-root-namespace.php';

        try {
            $result = $this->mapperBuilder()->mapper()->map(ChildBar::class, [
                'bar' => 'bar!',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('bar!', $result->bar);
    }
}
