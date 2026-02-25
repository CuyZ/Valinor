<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\AbstractObjectWithInterface;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Types\CallableType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use DateTimeImmutable;
use stdClass;

final class ReflectionClassDefinitionRepositoryTest extends UnitTestCase
{
    public function test_properties_can_be_retrieved(): void
    {
        $object = new class () {
            public string $propertyWithDefaultValue = 'Default value for property';

            /** @var bool */
            public $propertyWithDocBlockType;

            public $propertyWithNoType; // @phpstan-ignore-line

            public string $publicProperty;

            protected string $protectedProperty;

            private bool $privateProperty; // @phpstan-ignore-line
        };

        $type = new NativeClassType($object::class);
        $properties = $this->getClass($type)->properties;

        self::assertTrue($properties->get('propertyWithDefaultValue')->hasDefaultValue);
        self::assertSame('Default value for property', $properties->get('propertyWithDefaultValue')->defaultValue);

        self::assertInstanceOf(NativeBooleanType::class, $properties->get('propertyWithDocBlockType')->type);

        self::assertInstanceOf(MixedType::class, $properties->get('propertyWithNoType')->type);

        self::assertInstanceOf(StringType::class, $properties->get('publicProperty')->type);
        self::assertTrue($properties->get('publicProperty')->isPublic);

        self::assertInstanceOf(StringType::class, $properties->get('protectedProperty')->type);
        self::assertFalse($properties->get('protectedProperty')->isPublic);

        self::assertInstanceOf(NativeBooleanType::class, $properties->get('privateProperty')->type);
        self::assertFalse($properties->get('privateProperty')->isPublic);
    }

    public function test_methods_can_be_retrieved(): void
    {
        $object = new class () {
            public function __construct() {}

            /**
             * @param string $parameterWithDocBlockType
             * @phpstan-ignore-next-line
             */
            #[Constructor]
            public static function publicMethod(
                bool $mandatoryParameter,
                $parameterWithNoType,
                $parameterWithDocBlockType,
                string $optionalParameter = 'Optional parameter value',
            ): void {}

            #[Constructor]
            public static function publicMethodWithReturnType(): string
            {
                return 'foo';
            }

            /**
             * @return string
             */
            #[Constructor]
            public static function publicMethodWithDocBlockReturnType()
            {
                return 'foo';
            }

            #[Constructor]
            public static function publicMethodWithNativeAndDocBlockReturnTypes(): string
            {
                return 'foo';
            }
        };

        $className = $object::class;
        $type = new NativeClassType($className);
        $methods = $this->getClass($type)->methods;

        self::assertTrue($methods->hasConstructor());

        self::assertInstanceOf(StringType::class, $methods->get('publicMethodWithReturnType')->returnType);
        self::assertInstanceOf(StringType::class, $methods->get('publicMethodWithDocBlockReturnType')->returnType);
        self::assertInstanceOf(StringType::class, $methods->get('publicMethodWithNativeAndDocBlockReturnTypes')->returnType);

        $parameters = $methods->get('publicMethod')->parameters;

        self::assertTrue($parameters->has('mandatoryParameter'));
        self::assertTrue($parameters->has('parameterWithNoType'));
        self::assertTrue($parameters->has('parameterWithDocBlockType'));
        self::assertTrue($parameters->has('optionalParameter'));

        $mandatoryParameter = $parameters->get('mandatoryParameter');
        $parameterWithNoType = $parameters->get('parameterWithNoType');
        $parameterWithDocBlockType = $parameters->get('parameterWithDocBlockType');
        $optionalParameter = $parameters->get('optionalParameter');

        self::assertSame($className . '::publicMethod($mandatoryParameter)', $mandatoryParameter->signature);
        self::assertSame($className . '::publicMethod($parameterWithNoType)', $parameterWithNoType->signature);
        self::assertSame($className . '::publicMethod($parameterWithDocBlockType)', $parameterWithDocBlockType->signature);
        self::assertSame($className . '::publicMethod($optionalParameter)', $optionalParameter->signature);

        self::assertInstanceOf(NativeBooleanType::class, $mandatoryParameter->type);
        self::assertFalse($mandatoryParameter->isOptional);

        self::assertInstanceOf(MixedType::class, $parameterWithNoType->type);

        self::assertInstanceOf(StringType::class, $parameterWithDocBlockType->type);

        self::assertTrue($parameters->get('optionalParameter')->isOptional);
        self::assertSame('Optional parameter value', $optionalParameter->defaultValue);
    }

    public function test_generics_are_assigned_for_interface(): void
    {
        $type = new InterfaceType(SomeInterfaceWithGeneric::class, [NonEmptyStringType::get()]);
        $methods = $this->getClass($type)->methods;

        self::assertInstanceOf(NonEmptyStringType::class, $methods->get('map')->returnType);
    }

    public function test_generics_are_assigned_in_properties_types(): void
    {
        $class =
            (new /** @template T */ class () {
                /** @var callable(T): int */
                public $callableWithGeneric;

                /** @var \CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection\SomeInterfaceWithGeneric<T> */
                public SomeInterfaceWithGeneric $interfaceWithGeneric;

                /** @var stdClass&T */
                public $intersectionWithGeneric;
            })::class;

        $type = new NativeClassType($class, [new NativeClassType(DateTimeImmutable::class)]);
        $properties = $this->getClass($type)->properties;

        $callableWithGeneric = $properties->get('callableWithGeneric');
        $interfaceWithGeneric = $properties->get('interfaceWithGeneric');
        $intersectionWithGeneric = $properties->get('intersectionWithGeneric');

        self::assertInstanceOf(CallableType::class, $callableWithGeneric->type);
        self::assertSame('callable(DateTimeImmutable): int', $callableWithGeneric->type->toString());

        self::assertInstanceOf(InterfaceType::class, $interfaceWithGeneric->type);
        self::assertSame(SomeInterfaceWithGeneric::class . '<DateTimeImmutable>', $interfaceWithGeneric->type->toString());

        self::assertInstanceOf(IntersectionType::class, $intersectionWithGeneric->type);
        self::assertSame('stdClass&DateTimeImmutable', $intersectionWithGeneric->type->toString());
    }

    public function test_methods_can_be_retrieved_from_abstract_object_with_interface_and_with_method_referencing_self(): void
    {
        $type = new NativeClassType(AbstractObjectWithInterface::class);
        $methods = $this->getClass($type)->methods;

        self::assertTrue($methods->has('of'));
    }

    public function test_private_parent_constructor_is_listed_in_methods(): void
    {
        $type = new NativeClassType(ClassWithInheritedPrivateConstructor::class);
        $methods = $this->getClass($type)->methods;

        self::assertTrue($methods->hasConstructor());
        self::assertFalse($methods->constructor()->isPublic);
    }

    public function test_can_use_generic_type_from_imported_alias_type(): void
    {
        $type = new NativeClassType(SomeClassImportingAliasWithGeneric::class);
        $properties = $this->getClass($type)->properties;
        $propertyType = $properties->get('propertyWithGenericAlias')->type;

        self::assertSame('non-empty-array<string>', $propertyType->toString());
    }

    public function test_can_use_nested_local_types(): void
    {
        $class =
            /**
             * @phpstan-type SomeType = non-empty-string
             * @phpstan-type SomeNestedType = non-empty-array<SomeType>
             */
            (new class () {
                /** @var SomeNestedType */ // @phpstan-ignore class.notFound
                public $propertyWithNestedType;
            })::class;

        $type = new NativeClassType($class);
        $properties = $this->getClass($type)->properties;
        $propertyType = $properties->get('propertyWithNestedType')->type;

        self::assertSame('non-empty-array<non-empty-string>', $propertyType->toString());
    }

    public function test_phpstan_local_type_can_use_psalm_local_type(): void
    {
        $class =
            /**
             * @psalm-type SomeType = non-empty-string
             * @phpstan-type SomeNestedType = non-empty-array<SomeType>
             */
            (new class () {
                /** @var SomeNestedType */ // @phpstan-ignore class.notFound
                public $propertyWithNestedType;
            })::class;

        $type = new NativeClassType($class);
        $properties = $this->getClass($type)->properties;
        $propertyType = $properties->get('propertyWithNestedType')->type;

        self::assertSame('non-empty-array<non-empty-string>', $propertyType->toString());
    }

    public function test_psalm_local_type_can_use_phpstan_local_type(): void
    {
        $class =
            /**
             * @phpstan-type SomeType = non-empty-string
             * @psalm-type SomeNestedType = non-empty-array<SomeType>
             */
            (new class () {
                /** @var SomeNestedType */ // @phpstan-ignore class.notFound
                public $propertyWithNestedType;
            })::class;

        $type = new NativeClassType($class);
        $properties = $this->getClass($type)->properties;
        $propertyType = $properties->get('propertyWithNestedType')->type;

        self::assertSame('non-empty-array<non-empty-string>', $propertyType->toString());
    }

    public function test_invalid_property_type_is_marked_as_unresolvable(): void
    {
        $class = (new class () {
            /** @var InvalidType */
            public $propertyWithInvalidType; // @phpstan-ignore-line
        })::class;

        $class = $this->getClass(new NativeClassType($class));
        $type = $class->properties->get('propertyWithInvalidType')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The type `InvalidType` for property `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_property_default_value_is_marked_as_unresolvable(): void
    {
        $class = (new class () {
            /** @var string */
            public $propertyWithInvalidDefaultValue = false; // @phpstan-ignore-line
        })::class;

        $class = $this->getClass(new NativeClassType($class));
        $type = $class->properties->get('propertyWithInvalidDefaultValue')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/The type `string` for property `.*::\$propertyWithInvalidDefaultValue` could not be resolved: invalid default value false/', $type->message());
    }

    public function test_invalid_parameter_type_is_marked_as_unresolvable(): void
    {
        $class = (new class () {
            /**
             * @formatter:off
             * @param InvalidTypeWithPendingSpaces         $parameterWithInvalidType
             * @formatter:on
             */
            #[Constructor]
            public static function publicMethod($parameterWithInvalidType): void {} // @phpstan-ignore class.notFound
        })::class;

        $class = $this->getClass(new NativeClassType($class));
        $type = $class->methods->get('publicMethod')->parameters->get('parameterWithInvalidType')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The type `InvalidTypeWithPendingSpaces` for parameter `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_method_return_type_is_marked_as_unresolvable(): void
    {
        $class = (new class () {
            /**
             * @return InvalidType
             * @phpstan-ignore missingType.parameter, return.phpDocType
             */
            #[Constructor]
            public static function publicMethod($parameterWithInvalidType): void {} // @phpstan-ignore class.notFound
        })::class;

        $class = $this->getClass(new NativeClassType($class));
        $type = $class->methods->get('publicMethod')->returnType;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The return type `InvalidType` of method `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_parameter_default_value_is_marked_as_unresolvable(): void
    {
        $class = (new class () {
            /**
             * @param string $parameterWithInvalidDefaultValue
             */
            #[Constructor]
            public static function publicMethod($parameterWithInvalidDefaultValue = false): void {} // @phpstan-ignore parameter.defaultValue
        })::class;

        $class = $this->getClass(new NativeClassType($class));
        $type = $class->methods->get('publicMethod')->parameters->get('parameterWithInvalidDefaultValue')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/The type `string` for parameter `.*::publicMethod\(\$parameterWithInvalidDefaultValue\)` could not be resolved: invalid default value false/', $type->message());
    }

    public function test_method_with_non_matching_return_types_is_marked_as_unresolvable(): void
    {
        $class = (new class () {
            /**
             * @return bool
             * @phpstan-ignore-next-line
             */
            #[Constructor]
            public static function publicMethod(): string
            {
                return 'foo';
            }
        })::class;

        $returnType = $this
            ->getClass(new NativeClassType($class))
            ->methods
            ->get('publicMethod')
            ->returnType;

        self::assertInstanceOf(UnresolvableType::class, $returnType);
        self::assertMatchesRegularExpression('/The return type `bool` of method `.*::publicMethod\(\)` could not be resolved: `bool` \(docblock\) does not accept `string` \(native\)./', $returnType->message());
    }

    public function test_method_that_should_not_be_included_is_not_included(): void
    {
        $class = (new class () {
            public function publicMethod(): void {}
        })::class;

        $methods = $this
            ->getClass(new NativeClassType($class))
            ->methods;

        self::assertFalse($methods->has('publicMethod'));
    }

    public function test_class_with_local_type_alias_name_duplication_is_marked_as_unresolvable(): void
    {
        $className =
            /**
             * @template SomeTemplate
             * @template AnotherTemplate
             * @template YetAnotherTemplate
             * @psalm-type SomeTemplate = int
             * @phpstan-type AnotherTemplate = int
             */
            (new class () {
                /** @var SomeTemplate */
                public $someTemplate; // @phpstan-ignore-line

                /** @var AnotherTemplate */
                public $anotherTemplate; // @phpstan-ignore-line
            })::class;

        $class = $this->getClass(new NativeClassType($className, [new FakeType(), new FakeType(), new FakeType()]));

        $someTemplateProperty = $class->properties->get('someTemplate');
        $anotherTemplateProperty = $class->properties->get('anotherTemplate');

        self::assertInstanceOf(UnresolvableType::class, $someTemplateProperty->type);
        self::assertSame("The type `SomeTemplate` for property `$className::\$someTemplate` could not be resolved: collision for the type alias `SomeTemplate` that was declared 2 times.", $someTemplateProperty->type->message());

        self::assertInstanceOf(UnresolvableType::class, $anotherTemplateProperty->type);
        self::assertSame("The type `AnotherTemplate` for property `$className::\$anotherTemplate` could not be resolved: collision for the type alias `AnotherTemplate` that was declared 2 times.", $anotherTemplateProperty->type->message());
    }

    public function test_class_with_invalid_type_alias_is_marked_as_unresolvable(): void
    {
        $class =
            /**
             * @phpstan-type T = array{foo: string
             */
            (new class () {
                /** @var T */
                public $value; // @phpstan-ignore-line
            })::class;

        $type = $this->getClass(new NativeClassType($class))->properties->get('value')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The type `array{foo: string` for property `.*\$value` could not be resolved: the type `array{foo: string` for local alias `T` of the class `.*` could not be resolved: missing closing curly bracket in shaped array signature `array{foo: string`\.$/', $type->message());
    }

    public function test_template_with_invalid_value_for_property_subtype_returns_unresolvable_type(): void
    {
        $class =
            /**
             * @template T
             */
            (new class () {
                /** @var class-string<T> */
                public $value; // @phpstan-ignore class.notFound
            })::class;

        $type = $this->getClass(new NativeClassType($class, [new NativeFloatType()]))->properties->get('value')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/The type `class-string<T>` for property `.*` could not be resolved: invalid class string `class-string<float>`, each element must be a class name or an interface name but found `float`./', $type->message());
    }

    private function getClass(ObjectType $type): ClassDefinition
    {
        return $this->getService(ClassDefinitionRepository::class)->for($type);
    }
}

/**
 * @template T
 */
interface SomeInterfaceWithGeneric
{
    /**
     * @return T
     */
    public function map();
}

abstract class AbstractClassWithPrivateConstructor
{
    private function __construct() {}
}

final class ClassWithInheritedPrivateConstructor extends AbstractClassWithPrivateConstructor {}

/**
 * @template T
 * @phpstan-type ArrayOfGenericAlias = non-empty-array<T>
 */
class SomeClassWithGenericLocalAlias
{
    /** @var T */
    public $propertyWithLocalAlias;
}

/**
 * @phpstan-import-type ArrayOfGenericAlias from SomeClassWithGenericLocalAlias<string> (@phpstan-ignore-line)
 */
final class SomeClassImportingAliasWithGeneric
{
    /** @var ArrayOfGenericAlias */ // @phpstan-ignore class.notFound
    public $propertyWithGenericAlias;
}
