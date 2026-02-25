<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassParentTypeResolver;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class ClassParentTypeResolverTest extends UnitTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('class_parent_is_resolved_properly_data_provider')]
    public function test_class_parent_is_resolved_properly(string $className, string $expectedParent): void
    {
        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(new NativeClassType($className));

        self::assertSame($expectedParent, $parent->toString());
    }

    public static function class_parent_is_resolved_properly_data_provider(): iterable
    {
        yield 'class extending generic parent with two templates' => [
            'className' => SomeClassExtendingParent::class,
            'expectedParent' => SomeAbstractClassDefiningTwoTemplates::class . '<non-empty-string, int<42, 1337>>',
        ];
    }

    public function test_several_extends_tags_sets_unresolvable_type_in_generic(): void
    {
        $class =
            /**
             * @extends SomeAbstractClassDefiningTwoTemplates<string, int>
             * @extends SomeAbstractClassDefiningTwoTemplates<int, string>
             */
            (new class () extends SomeAbstractClassDefiningTwoTemplates {})::class;

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(new NativeClassType($class));

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

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(new NativeClassType($class));

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

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(new NativeClassType($class));

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

        $parent = $this->classParentTypeResolver()->resolveParentTypeFor(new NativeClassType($class));

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
abstract class SomeAbstractClassDefiningTwoTemplates {}

/**
 * @extends SomeAbstractClassDefiningTwoTemplates<non-empty-string, int<42, 1337>>
 */
final class SomeClassExtendingParent extends SomeAbstractClassDefiningTwoTemplates {}
