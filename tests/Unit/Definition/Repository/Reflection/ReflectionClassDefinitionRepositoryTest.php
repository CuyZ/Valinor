<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Exception\ClassTypeAliasesDuplication;
use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClass;
use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClassType;
use CuyZ\Valinor\Definition\Exception\TypesDoNotMatch;
use CuyZ\Valinor\Definition\Exception\UnknownTypeAliasImport;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeAttributesRepository;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Factory\FakeTypeParserFactory;
use CuyZ\Valinor\Tests\Fixture\Object\AbstractObjectWithInterface;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ReflectionClassDefinitionRepositoryTest extends TestCase
{
    private ReflectionClassDefinitionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ReflectionClassDefinitionRepository(
            new FakeTypeParserFactory(),
            new FakeAttributesRepository(),
        );
    }

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
        $properties = $this->repository->for($type)->properties();

        self::assertTrue($properties->get('propertyWithDefaultValue')->hasDefaultValue());
        self::assertSame('Default value for property', $properties->get('propertyWithDefaultValue')->defaultValue());

        self::assertInstanceOf(NativeBooleanType::class, $properties->get('propertyWithDocBlockType')->type());

        self::assertInstanceOf(MixedType::class, $properties->get('propertyWithNoType')->type());

        self::assertInstanceOf(StringType::class, $properties->get('publicProperty')->type());
        self::assertTrue($properties->get('publicProperty')->isPublic());

        self::assertInstanceOf(StringType::class, $properties->get('protectedProperty')->type());
        self::assertFalse($properties->get('protectedProperty')->isPublic());

        self::assertInstanceOf(NativeBooleanType::class, $properties->get('privateProperty')->type());
        self::assertFalse($properties->get('privateProperty')->isPublic());
    }

    public function test_methods_can_be_retrieved(): void
    {
        $object = new class () {
            public function __construct() {}

            /**
             * @param string $parameterWithDocBlockType
             * @phpstan-ignore-next-line
             */
            public function publicMethod(
                bool $mandatoryParameter,
                $parameterWithNoType,
                $parameterWithDocBlockType,
                string $optionalParameter = 'Optional parameter value'
            ): void {}

            public function publicMethodWithReturnType(): string
            {
                return 'foo';
            }

            /**
             * @return string
             */
            public function publicMethodWithDocBlockReturnType()
            {
                return 'foo';
            }

            public function publicMethodWithNativeAndDocBlockReturnTypes(): string
            {
                return 'foo';
            }
        };

        $className = $object::class;
        $type = new NativeClassType($className);
        $methods = $this->repository->for($type)->methods();

        self::assertTrue($methods->hasConstructor());

        self::assertInstanceOf(StringType::class, $methods->get('publicMethodWithReturnType')->returnType());
        self::assertInstanceOf(StringType::class, $methods->get('publicMethodWithDocBlockReturnType')->returnType());
        self::assertInstanceOf(StringType::class, $methods->get('publicMethodWithNativeAndDocBlockReturnTypes')->returnType());

        $parameters = $methods->get('publicMethod')->parameters();

        self::assertTrue($parameters->has('mandatoryParameter'));
        self::assertTrue($parameters->has('parameterWithNoType'));
        self::assertTrue($parameters->has('parameterWithDocBlockType'));
        self::assertTrue($parameters->has('optionalParameter'));

        $mandatoryParameter = $parameters->get('mandatoryParameter');
        $parameterWithNoType = $parameters->get('parameterWithNoType');
        $parameterWithDocBlockType = $parameters->get('parameterWithDocBlockType');
        $optionalParameter = $parameters->get('optionalParameter');

        self::assertSame($className . '::publicMethod($mandatoryParameter)', $mandatoryParameter->signature());
        self::assertSame($className . '::publicMethod($parameterWithNoType)', $parameterWithNoType->signature());
        self::assertSame($className . '::publicMethod($parameterWithDocBlockType)', $parameterWithDocBlockType->signature());
        self::assertSame($className . '::publicMethod($optionalParameter)', $optionalParameter->signature());

        self::assertInstanceOf(NativeBooleanType::class, $mandatoryParameter->type());
        self::assertFalse($mandatoryParameter->isOptional());

        self::assertInstanceOf(MixedType::class, $parameterWithNoType->type());

        self::assertInstanceOf(StringType::class, $parameterWithDocBlockType->type());

        self::assertTrue($parameters->get('optionalParameter')->isOptional());
        self::assertSame('Optional parameter value', $optionalParameter->defaultValue());
    }

    public function test_methods_can_be_retrieved_from_abstract_object_with_interface_and_with_method_referencing_self(): void
    {
        $type = new NativeClassType(AbstractObjectWithInterface::class);
        $methods = $this->repository->for($type)->methods();

        self::assertTrue($methods->has('of'));
        self::assertTrue($methods->has('jsonSerialize'));
    }

    public function test_private_parent_constructor_is_listed_in_methods(): void
    {
        $type = new NativeClassType(ClassWithInheritedPrivateConstructor::class, parent: new NativeClassType(AbstractClassWithPrivateConstructor::class));
        $methods = $this->repository->for($type)->methods();

        self::assertTrue($methods->hasConstructor());
        self::assertFalse($methods->constructor()->isPublic());
    }

    public function test_invalid_property_type_throws_exception(): void
    {
        $class = (new class () {
            /** @var InvalidType */
            public $propertyWithInvalidType; // @phpstan-ignore-line
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->properties()->get('propertyWithInvalidType')->type();

        self::assertInstanceOf(UnresolvableType::class, $type);
        /** @var UnresolvableType $type */
        self::assertMatchesRegularExpression('/^The type `InvalidType` for property `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_property_default_value_throws_exception(): void
    {
        $class = (new class () {
            /** @var string */
            public $propertyWithInvalidDefaultValue = false; // @phpstan-ignore-line
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->properties()->get('propertyWithInvalidDefaultValue')->type();

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/Property `.*::\$propertyWithInvalidDefaultValue` of type `string` has invalid default value false/', $type->message());
    }

    public function test_property_with_non_matching_types_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @phpstan-ignore-next-line
             * @var string
             */
            public bool $propertyWithNotMatchingTypes;
        })::class;

        $this->expectException(TypesDoNotMatch::class);
        $this->expectExceptionCode(1638471381);
        $this->expectExceptionMessage("Types for property `$class::\$propertyWithNotMatchingTypes` do not match: `string` (docblock) does not accept `bool` (native).");

        $this->repository->for(new NativeClassType($class));
    }

    public function test_invalid_parameter_type_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @formatter:off
             * @param InvalidTypeWithPendingSpaces         $parameterWithInvalidType
             * @formatter:on
             * @phpstan-ignore-next-line
             */
            public function publicMethod($parameterWithInvalidType): void {}
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->methods()->get('publicMethod')->parameters()->get('parameterWithInvalidType')->type();

        self::assertInstanceOf(UnresolvableType::class, $type);
        /** @var UnresolvableType $type */
        self::assertMatchesRegularExpression('/^The type `InvalidTypeWithPendingSpaces` for parameter `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_method_return_type_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @return InvalidType
             * @phpstan-ignore-next-line
             */
            public function publicMethod($parameterWithInvalidType): void {}
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->methods()->get('publicMethod')->returnType();

        self::assertInstanceOf(UnresolvableType::class, $type);
        /** @var UnresolvableType $type */
        self::assertMatchesRegularExpression('/^The type `InvalidType` for return type of method `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_parameter_default_value_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @param string $parameterWithInvalidDefaultValue
             * @phpstan-ignore-next-line
             */
            public function publicMethod($parameterWithInvalidDefaultValue = false): void {}
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->methods()->get('publicMethod')->parameters()->get('parameterWithInvalidDefaultValue')->type();

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/Parameter `.*::publicMethod\(\$parameterWithInvalidDefaultValue\)` of type `string` has invalid default value false/', $type->message());
    }

    public function test_parameter_with_non_matching_types_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @param string $parameterWithNotMatchingTypes
             * @phpstan-ignore-next-line
             */
            public function publicMethod(bool $parameterWithNotMatchingTypes): void {}
        })::class;

        $this->expectException(TypesDoNotMatch::class);
        $this->expectExceptionCode(1638471381);
        $this->expectExceptionMessage("Types for parameter `$class::publicMethod(\$parameterWithNotMatchingTypes)` do not match: `string` (docblock) does not accept `bool` (native).");

        $this->repository->for(new NativeClassType($class));
    }

    public function test_method_with_non_matching_return_types_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @return bool
             * @phpstan-ignore-next-line
             */
            public function publicMethod(): string
            {
                return 'foo';
            }
        })::class;

        $this->expectException(TypesDoNotMatch::class);
        $this->expectExceptionCode(1638471381);
        $this->expectExceptionMessage("Return types for method `$class::publicMethod()` do not match: `bool` (docblock) does not accept `string` (native).");

        $this->repository->for(new NativeClassType($class));
    }

    public function test_class_with_local_type_alias_name_duplication_throws_exception(): void
    {
        $class =
            /**
             * @template T
             * @template AnotherTemplate
             * @psalm-type T = int
             * @phpstan-type AnotherTemplate = int
             */
            (new class () {
                /** @var T */
                public $value; // @phpstan-ignore-line
            })::class;

        $this->expectException(ClassTypeAliasesDuplication::class);
        $this->expectExceptionCode(1638477604);
        $this->expectExceptionMessage("The following type aliases already exist in class `$class`: `T`, `AnotherTemplate`.");

        $this->repository->for(new NativeClassType($class, ['T' => new FakeType(), 'AnotherTemplate' => new FakeType()]));
    }

    public function test_class_with_invalid_type_alias_import_class_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from UnknownType
             */
            (new class () {
                /** @var T */
                public $value; // @phpstan-ignore-line
            })::class;

        $this->expectException(InvalidTypeAliasImportClass::class);
        $this->expectExceptionCode(1638535486);
        $this->expectExceptionMessage("Cannot import a type alias from unknown class `UnknownType` in class `$class`.");

        $this->repository->for(new NativeClassType($class));
    }

    public function test_class_with_invalid_type_alias_import_class_type_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from string
             */
            (new class () {
                /** @var T */
                public $value; // @phpstan-ignore-line
            })::class;

        $this->expectException(InvalidTypeAliasImportClassType::class);
        $this->expectExceptionCode(1638535608);
        $this->expectExceptionMessage("Importing a type alias can only be done with classes, `string` was given in class `$class`.");

        $this->repository->for(new NativeClassType($class));
    }

    public function test_class_with_unknown_type_alias_import_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from stdClass
             */
            (new class () {
                /** @var T */
                public $value; // @phpstan-ignore-line
            })::class;

        $this->expectException(UnknownTypeAliasImport::class);
        $this->expectExceptionCode(1638535757);
        $this->expectExceptionMessage("Type alias `T` imported in `$class` could not be found in `stdClass`");

        $this->repository->for(new NativeClassType($class));
    }
}

abstract class AbstractClassWithPrivateConstructor
{
    private function __construct() {}
}

final class ClassWithInheritedPrivateConstructor extends AbstractClassWithPrivateConstructor {}
