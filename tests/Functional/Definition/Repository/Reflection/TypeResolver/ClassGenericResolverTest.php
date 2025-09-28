<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassGenericResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\TestCase;

final class ClassGenericResolverTest extends TestCase
{
    private ClassGenericResolver $genericResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->genericResolver = new ClassGenericResolver(
            new TypeParserFactory(),
        );
    }

    public function test_duplicated_template_name_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template TemplateA
             * @template TemplateA
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeIntegerType::get(), NativeStringType::get()]);

        $generics = $this->genericResolver->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['TemplateA']);
        self::assertSame("The template `TemplateA` in `$className` was defined at least twice.", $generics['TemplateA']->message());
    }

    public function test_invalid_template_type_sets_unresolvable_type_for_generic(): void
    {
        $className =
            /**
             * @template Template of InvalidType
             */
            (new class () {})::class;

        $type = new NativeClassType($className, [NativeBooleanType::get()]);

        $generics = $this->genericResolver->resolveGenerics($type);

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

        $generics = $this->genericResolver->resolveGenerics($type);

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

        $generics = $this->genericResolver->resolveGenerics($type);

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

        $generics = $this->genericResolver->resolveGenerics($type);

        self::assertInstanceOf(UnresolvableType::class, $generics['Template']);
        self::assertSame('some message', $generics['Template']->message());
    }
}
