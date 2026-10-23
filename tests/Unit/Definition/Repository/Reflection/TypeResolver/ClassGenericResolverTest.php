<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassGenericResolver;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

final class ClassGenericResolverTest extends UnitTestCase
{
    public function test_duplicated_template_name_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template TemplateA
             * @template TemplateA
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeIntegerType::get(), NativeStringType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['TemplateA']);
        self::assertSame("The template `TemplateA` in `$className` was defined at least twice.", $generics['TemplateA']->message());
    }

    public function test_template_after_duplicated_template_name_is_still_resolved(): void
    {
        $className =
            /**
             * @template TemplateA
             * @template TemplateA
             * @template TemplateB
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeIntegerType::get(), NativeStringType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['TemplateA']);
        self::assertSame('string', $generics['TemplateB']->toString());
    }

    public function test_invalid_template_type_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template Template of InvalidType
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeBooleanType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['Template']);
        self::assertSame("Invalid template `Template` for `$className`: cannot parse unknown symbol `InvalidType`.", $generics['Template']->message());
    }

    public function test_generic_with_non_matching_type_for_template_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template Template of string
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeBooleanType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['Template']);
        self::assertSame("The generic `bool` is not a subtype of `string` for the template `Template` of the class `$className`.", $generics['Template']->message());
    }

    public function test_generic_with_non_matching_array_key_type_for_template_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template Template of array-key
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeBooleanType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['Template']);
        self::assertSame("The generic `bool` is not a subtype of `array-key` for the template `Template` of the class `$className`.", $generics['Template']->message());
    }

    public function test_unresolvable_type_generic_is_used(): void
    {
        $className =
            /**
             * @template Template
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [new UnresolvableType('InvalidType', 'some message')]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['Template']);
        self::assertSame('some message', $generics['Template']->message());
    }

    public function test_covariant_template_is_resolved(): void
    {
        $className =
            /**
             * @template-covariant T
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeStringType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertArrayHasKey('T', $generics);
        self::assertSame('string', $generics['T']->toString());
    }

    public function test_phpstan_covariant_template_is_resolved(): void
    {
        $className =
            /**
             * @phpstan-template-covariant T
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeStringType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertArrayHasKey('T', $generics);
        self::assertSame('string', $generics['T']->toString());
    }

    public function test_psalm_covariant_template_is_resolved(): void
    {
        $className =
            /**
             * @psalm-template-covariant T
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeStringType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertArrayHasKey('T', $generics);
        self::assertSame('string', $generics['T']->toString());
    }

    public function test_covariant_template_with_constraint_is_resolved(): void
    {
        $className =
            /**
             * @template-covariant T of string
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeStringType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertArrayHasKey('T', $generics);
        self::assertSame('string', $generics['T']->toString());
    }

    public function test_covariant_template_with_non_matching_type_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template-covariant T of string
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeBooleanType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['T']);
        self::assertSame("The generic `bool` is not a subtype of `string` for the template `T` of the class `$className`.", $generics['T']->message());
    }

    public function test_template_default_is_used_when_generic_is_omitted(): void
    {
        $className =
            /**
             * @template T = string
             */
            (new class () {})::class;

        $type = new NativeClassType($className, []);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertArrayHasKey('T', $generics);
        self::assertSame('string', $generics['T']->toString());
    }

    public function test_template_default_with_bound_is_used_when_generic_is_omitted(): void
    {
        $className =
            /**
             * @template T of string|int = int
             */
            (new class () {})::class;

        $type = new NativeClassType($className, []);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertArrayHasKey('T', $generics);
        self::assertSame('int', $generics['T']->toString());
    }

    public function test_assigned_generic_for_required_template_is_combined_with_default_of_trailing_template(): void
    {
        $className =
            /**
             * @template TemplateA
             * @template TemplateB = string
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeIntegerType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertSame('int', $generics['TemplateA']->toString());
        self::assertSame('string', $generics['TemplateB']->toString());
    }

    public function test_template_without_default_and_no_assigned_generic_falls_back_to_template_type(): void
    {
        $className =
            /**
             * @template Template of string
             */
            (new class () {})::class;

        $type = new NativeClassType($className, []);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertSame('Template of string', $generics['Template']->toString());
    }

    public function test_template_with_of_keyword_but_no_bound_resolves_to_mixed_bound(): void
    {
        $className =
            /**
             * @template Template of
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeStringType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertSame('string', $generics['Template']->toString());
    }

    public function test_assigned_generic_takes_precedence_over_template_default(): void
    {
        $className =
            /**
             * @template T = string
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeIntegerType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertArrayHasKey('T', $generics);
        self::assertSame('int', $generics['T']->toString());
    }

    public function test_template_default_not_matching_bound_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template T of string = int
             */
            (new class () {})::class;

        $type = new NativeClassType($className, []);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['T']);
        self::assertSame("The generic `int` is not a subtype of `string` for the template `T` of the class `$className`.", $generics['T']->message());
    }

    public function test_invalid_template_default_type_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template T = InvalidType
             */
            (new class () {})::class;

        $type = new NativeClassType($className, []);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['T']);
        self::assertSame("Invalid template `T` for `$className`: cannot parse unknown symbol `InvalidType`.", $generics['T']->message());
    }

    public function test_template_with_empty_default_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template T =
             */
            (new class () {})::class;

        $type = new NativeClassType($className, []);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['T']);
        self::assertSame("The template `T` in `$className` has no default type declared after `=`.", $generics['T']->message());
    }

    public function test_required_template_after_defaulted_template_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template A = string
             * @template B
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeIntegerType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['B']);
        self::assertSame("The template `B` in `$className` has no default type but is defined after the template `A` which declares one; templates with a default type must be defined last.", $generics['B']->message());
    }

    public function test_template_after_required_template_following_defaulted_template_is_still_resolved(): void
    {
        $className =
            /**
             * @template TemplateA = string
             * @template TemplateB
             * @template TemplateC = int
             */
            (new class () {})::class;

        $type = new NativeClassType($className, []);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertSame('string', $generics['TemplateA']->toString());
        self::assertInstanceOf(UnresolvableType::class, $generics['TemplateB']);
        self::assertSame('int', $generics['TemplateC']->toString());
    }

    public function test_required_template_after_empty_default_template_does_not_leak_generic(): void
    {
        $className =
            /**
             * @template A =
             * @template B
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeIntegerType::get()]);

        $generics = $this->classGenericResolver()->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['A']);
        self::assertSame("The template `A` in `$className` has no default type declared after `=`.", $generics['A']->message());

        self::assertInstanceOf(UnresolvableType::class, $generics['B']);
        self::assertSame("The template `B` in `$className` has no default type but is defined after the template `A` which declares one; templates with a default type must be defined last.", $generics['B']->message());
    }

    private function classGenericResolver(): ClassGenericResolver
    {
        return new ClassGenericResolver(
            new TypeParserFactory(),
        );
    }
}
