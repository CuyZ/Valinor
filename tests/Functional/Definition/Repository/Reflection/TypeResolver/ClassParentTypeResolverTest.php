<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Exception\ExtendTagTypeError;
use CuyZ\Valinor\Definition\Exception\InvalidExtendTagClassName;
use CuyZ\Valinor\Definition\Exception\InvalidExtendTagType;
use CuyZ\Valinor\Definition\Exception\SeveralExtendTagsFound;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassParentTypeResolver;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeClassType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassParentTypeResolverTest extends TestCase
{
    private ClassParentTypeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ClassParentTypeResolver(
            new LexingTypeParserFactory(),
        );
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('class_parent_is_resolved_properly_data_provider')]
    public function test_class_parent_is_resolved_properly(string $className, string $expectedParent): void
    {
        $parent = $this->resolver->resolveParentTypeFor(new NativeClassType($className));

        self::assertSame($expectedParent, $parent->toString());
    }

    public static function class_parent_is_resolved_properly_data_provider(): iterable
    {
        yield 'class extending generic parent with two templates' => [
            'className' => SomeClassExtendingParent::class,
            'expectedParent' => SomeAbstractClassDefiningTwoTemplates::class . '<non-empty-string, int<42, 1337>>',
        ];
    }

    public function test_several_extends_tags_throws_exception(): void
    {
        $class =
            /**
             * @extends stdClass<string>
             * @extends stdClass<string>
             */
            (new class () {})::class;

        $this->expectException(SeveralExtendTagsFound::class);
        $this->expectExceptionCode(1670195494);
        $this->expectExceptionMessage("Only one `@extends` tag should be set for the class `$class`.");

        $this->resolver->resolveParentTypeFor(new NativeClassType($class));
    }

    public function test_extend_tag_type_error_throws_exception(): void
    {
        $class =
            /**
             * @extends stdClass<InvalidType>
             */
            (new class () {})::class;

        $this->expectException(ExtendTagTypeError::class);
        $this->expectExceptionCode(1670193574);
        $this->expectExceptionMessage("The `@extends` tag of the class `$class` is not valid: Cannot parse unknown symbol `InvalidType`.");

        $this->resolver->resolveParentTypeFor(new NativeClassType($class));
    }

    public function test_invalid_extends_tag_throws_exception(): void
    {
        $class =
            /**
             * @extends string
             */
            (new class () extends stdClass {})::class;

        $this->expectException(InvalidExtendTagType::class);
        $this->expectExceptionCode(1670181134);
        $this->expectExceptionMessage("The `@extends` tag of the class `$class` has invalid type `string`, it should be `stdClass`.");

        $this->resolver->resolveParentTypeFor(new NativeClassType($class));
    }

    public function test_invalid_extends_tag_class_name_throws_exception(): void
    {
        $class =
            /**
             * @extends stdClass
             */
            (new class () extends SomeAbstractClassDefiningTwoTemplates {})::class;

        $this->expectException(InvalidExtendTagClassName::class);
        $this->expectExceptionCode(1670183564);
        $this->expectExceptionMessage("The `@extends` tag of the class `$class` has invalid class `stdClass`, it should be `" . SomeAbstractClassDefiningTwoTemplates::class . "`.");

        $this->resolver->resolveParentTypeFor(new NativeClassType($class));
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
