<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassParentTypeResolver;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterface;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterfaceWithGenericParent;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterfaceWithGenericParentAndNoExtendTag;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterfaceWithSeveralParents;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\GrandChildInterface;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use ReflectionProperty;
use stdClass;

final class ClassParentTypeResolverTest extends UnitTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('class_parent_is_resolved_properly_data_provider')]
    public function test_class_parent_is_resolved_properly(string $className, string $expectedParent): void
    {
        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(
            new NativeClassType($className),
            new ReflectionProperty($className, 'inheritedProperty'),
        );

        self::assertSame($expectedParent, $parent->toString());
    }

    public static function class_parent_is_resolved_properly_data_provider(): iterable
    {
        yield 'class extending generic parent with two templates' => [
            'className' => SomeClassExtendingParent::class,
            'expectedParent' => SomeAbstractClassDefiningTwoTemplates::class . '<non-empty-string, int<42, 1337>>',
        ];
    }

    /**
     * @param class-string $className
     */
    #[RequiresPhp('>=8.4')]
    #[DataProvider('interface_parent_is_resolved_properly_data_provider')]
    public function test_interface_parent_is_resolved_properly(string $className, string $memberName, string $expectedParent): void
    {
        $member = Reflection::class($className)->getProperty($memberName);

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(new InterfaceType($className), $member);

        self::assertSame($expectedParent, $parent->toString());
    }

    public static function interface_parent_is_resolved_properly_data_provider(): iterable
    {
        yield 'member from grandparent is resolved to the direct parent interface' => [
            'className' => GrandChildInterface::class,
            'memberName' => 'deleted',
            'expectedParent' => ChildInterface::class,
        ];

        yield 'member declared in the direct parent interface' => [
            'className' => GrandChildInterface::class,
            'memberName' => 'name',
            'expectedParent' => ChildInterface::class,
        ];

        yield 'member declared in the interface itself falls back to that interface' => [
            'className' => GrandChildInterface::class,
            'memberName' => 'active',
            'expectedParent' => GrandChildInterface::class,
        ];

        yield 'member from the first of several parent interfaces' => [
            'className' => ChildInterfaceWithSeveralParents::class,
            'memberName' => 'deleted',
            'expectedParent' => \CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\BaseInterface::class,
        ];

        yield 'member from the second of several parent interfaces' => [
            'className' => ChildInterfaceWithSeveralParents::class,
            'memberName' => 'count',
            'expectedParent' => \CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\OtherBaseInterface::class,
        ];

        yield 'generic parent interface is resolved with its generics from the extends tag' => [
            'className' => ChildInterfaceWithGenericParent::class,
            'memberName' => 'genericValue',
            'expectedParent' => 'CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\GenericBaseInterface<string>',
        ];
    }

    #[RequiresPhp('>=8.4')]
    public function test_generic_parent_interface_without_extends_tag_sets_unresolvable_type_in_generic(): void
    {
        $className = ChildInterfaceWithGenericParentAndNoExtendTag::class;

        $member = Reflection::class($className)->getProperty('genericValue');

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(new InterfaceType($className), $member);

        self::assertInstanceOf(InterfaceType::class, $parent);
        self::assertInstanceOf(UnresolvableType::class, $parent->generics()[0]);
        self::assertSame(
            "The `@extends` tag of the class `$className` is not valid: there are 1 missing generics for `" . 'CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\GenericBaseInterface<?>`.',
            $parent->generics()[0]->message(),
        );
    }

    public function test_several_extends_tags_sets_unresolvable_type_in_generic(): void
    {
        $class =
            /**
             * @extends SomeAbstractClassDefiningTwoTemplates<string, int>
             * @extends SomeAbstractClassDefiningTwoTemplates<int, string>
             */
            (new class () extends SomeAbstractClassDefiningTwoTemplates {})::class;

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(
            new NativeClassType($class),
            new ReflectionProperty($class, 'inheritedProperty'),
        );

        self::assertInstanceOf(NativeClassType::class, $parent);
        self::assertInstanceOf(UnresolvableType::class, $parent->generics()[0]);
        self::assertSame("Only one `@extends` tag should be set for the class `$class`.", $parent->generics()[0]->message());
    }

    public function test_extend_tag_type_error_sets_unresolvable_type_in_generic(): void
    {
        $class =
            /**
             * @extends SomeAbstractClassDefiningTwoTemplates<array<string>
             */
            (new class () extends SomeAbstractClassDefiningTwoTemplates {})::class;

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(
            new NativeClassType($class),
            new ReflectionProperty($class, 'inheritedProperty'),
        );

        self::assertInstanceOf(NativeClassType::class, $parent);
        self::assertInstanceOf(UnresolvableType::class, $parent->generics()[0]);
        self::assertSame("The `@extends` tag of the class `$class` is not valid: the closing bracket is missing for the generic `" . SomeAbstractClassDefiningTwoTemplates::class . "<array<string>>`.", $parent->generics()[0]->message());
    }

    public function test_invalid_extends_tag_sets_unresolvable_type_in_generic(): void
    {
        $class =
            /**
             * @extends string
             */
            (new class () extends SomeAbstractClassDefiningTwoTemplates {})::class;

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(
            new NativeClassType($class),
            new ReflectionProperty($class, 'inheritedProperty'),
        );

        self::assertInstanceOf(NativeClassType::class, $parent);
        self::assertInstanceOf(UnresolvableType::class, $parent->generics()[0]);
        self::assertSame("The `@extends` tag of the class `$class` has invalid type `string`, it should be `" . SomeAbstractClassDefiningTwoTemplates::class . '`.', $parent->generics()[0]->message());
    }

    public function test_invalid_extends_tag_class_name_sets_unresolvable_type_in_generic(): void
    {
        $class =
            /**
             * @extends stdClass
             */
            (new class () extends SomeAbstractClassDefiningTwoTemplates {})::class;

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(
            new NativeClassType($class),
            new ReflectionProperty($class, 'inheritedProperty'),
        );

        self::assertInstanceOf(NativeClassType::class, $parent);
        self::assertInstanceOf(UnresolvableType::class, $parent->generics()[0]);
        self::assertSame("The `@extends` tag of the class `$class` has invalid type `stdClass`, it should be `" . SomeAbstractClassDefiningTwoTemplates::class . "`.", $parent->generics()[0]->message());
    }

    private function classParentTypeResolver(): ClassParentTypeResolver
    {
        return new ClassParentTypeResolver(
            new TypeParserFactory(),
        );
    }
}

/**
 * @template TemplateA
 * @template TemplateB
 */
abstract class SomeAbstractClassDefiningTwoTemplates
{
    public mixed $inheritedProperty = null;
}

/**
 * @extends SomeAbstractClassDefiningTwoTemplates<non-empty-string, int<42, 1337>>
 */
final class SomeClassExtendingParent extends SomeAbstractClassDefiningTwoTemplates {}
