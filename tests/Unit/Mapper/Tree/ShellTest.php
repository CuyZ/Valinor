<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributeDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Functional\FunctionalTestCase;
use CuyZ\Valinor\Type\Dumper\TypeDumper;

final class ShellTest extends FunctionalTestCase
{
    public function test_type_and_value_can_be_retrieved(): void
    {
        $type = new FakeType();
        $value = 'foo';

        $shell = Shell::root(new Settings(), $this->getService(TypeDumper::class), $type, $value);

        self::assertSame($type, $shell->type());
        self::assertSame($value, $shell->value());
    }

    public function test_root_path_is_fixed(): void
    {
        $shell = Shell::root(new Settings(), $this->getService(TypeDumper::class), new FakeType(), 'foo');

        self::assertSame('*root*', $shell->path());
    }

    public function test_change_type_changes_type(): void
    {
        $typeA = new FakeType();
        $typeB = FakeType::matching($typeA);

        $shellA = Shell::root(new Settings(), $this->getService(TypeDumper::class), $typeA, []);
        $shellB = $shellA->withType($typeB);

        self::assertNotSame($shellA, $shellB);
        self::assertSame($typeB, $shellB->type());
    }

    public function test_allows_superfluous_keys(): void
    {
        $shellA = Shell::root(new Settings(), $this->getService(TypeDumper::class), new FakeType(), []);
        $shellB = $shellA->withAllowedSuperfluousKeys(['foo', 'bar']);

        self::assertNotSame($shellA, $shellB);
        self::assertSame(['foo', 'bar'], $shellB->allowedSuperfluousKeys());
    }

    public function test_change_value_changes_value(): void
    {
        $valueA = 'foo';
        $valueB = 'bar';

        $shellA = Shell::root(new Settings(), $this->getService(TypeDumper::class), new FakeType(), $valueA);
        $shellB = $shellA->withValue($valueB);

        self::assertNotSame($shellA, $shellB);
        self::assertSame($valueB, $shellB->value());
    }

    public function test_root_shell_is_root(): void
    {
        $shell = Shell::root(new Settings(), $this->getService(TypeDumper::class), new FakeType(), []);

        self::assertTrue($shell->isRoot());
        self::assertSame('', $shell->name());
    }

    public function test_shell_child_values_can_be_retrieved(): void
    {
        $value = 'some value';
        $type = FakeType::permissive();

        $shell = Shell::root(new Settings(), $this->getService(TypeDumper::class), new FakeType(), []);
        $child = $shell->child('foo', $type)->withValue($value);

        self::assertFalse($child->isRoot());
        self::assertSame('foo', $child->name());
        self::assertSame('foo', $child->path());
        self::assertSame($type, $child->type());
        self::assertSame($value, $child->value());
    }

    public function test_can_merge_attributes_for_shell(): void
    {
        $attributeA = FakeAttributeDefinition::new();
        $attributeB = FakeAttributeDefinition::new();
        $attributesA = new Attributes($attributeA);
        $attributesB = new Attributes($attributeB);

        $shellA = Shell::root(new Settings(), $this->getService(TypeDumper::class), new FakeType(), []);
        $shellB = $shellA->withAttributes($attributesA);
        $shellC = $shellB->withAttributes($attributesB);

        self::assertNotSame($shellA, $shellB);
        ;
        self::assertNotSame($shellB, $shellC);
        ;
        self::assertSame([$attributeA], $shellB->attributes()->toArray());
        self::assertSame([$attributeA, $attributeB], $shellC->attributes()->toArray());
    }

    public function test_can_transform_iterator_to_array(): void
    {
        $value = (function () {
            yield 'foo' => 'foo';
            yield 'bar' => 'bar';
        })();

        $shellA = Shell::root(new Settings(), $this->getService(TypeDumper::class), new FakeType(), $value);
        $shellB = $shellA->transformIteratorToArray();

        self::assertNotSame($shellA, $shellB);
        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $shellB->value());
    }
}
