<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Exception\ClassTypeAliasesDuplication;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\AbstractObjectWithInterface;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\TestCase;

final class ReflectionClassDefinitionRepositoryTest extends TestCase
{
    private ReflectionClassDefinitionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ReflectionClassDefinitionRepository(
            new LexingTypeParserFactory(),
            [],
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
        $properties = $this->repository->for($type)->properties;

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
                string $optionalParameter = 'Optional parameter value'
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
        $methods = $this->repository->for($type)->methods;

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

    public function test_methods_can_be_retrieved_from_abstract_object_with_interface_and_with_method_referencing_self(): void
    {
        $type = new NativeClassType(AbstractObjectWithInterface::class);
        $methods = $this->repository->for($type)->methods;

        self::assertTrue($methods->has('of'));
    }

    public function test_private_parent_constructor_is_listed_in_methods(): void
    {
        $type = new NativeClassType(ClassWithInheritedPrivateConstructor::class);
        $methods = $this->repository->for($type)->methods;

        self::assertTrue($methods->hasConstructor());
        self::assertFalse($methods->constructor()->isPublic);
    }

    public function test_invalid_property_type_throws_exception(): void
    {
        $class = (new class () {
            /** @var InvalidType */
            public $propertyWithInvalidType; // @phpstan-ignore-line
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->properties->get('propertyWithInvalidType')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The type `InvalidType` for property `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_property_default_value_throws_exception(): void
    {
        $class = (new class () {
            /** @var string */
            public $propertyWithInvalidDefaultValue = false; // @phpstan-ignore-line
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->properties->get('propertyWithInvalidDefaultValue')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/Property `.*::\$propertyWithInvalidDefaultValue` of type `string` has invalid default value false/', $type->message());
    }

    public function test_invalid_parameter_type_throws_exception(): void
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

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->methods->get('publicMethod')->parameters->get('parameterWithInvalidType')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The type `InvalidTypeWithPendingSpaces` for parameter `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_method_return_type_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @return InvalidType
             * @phpstan-ignore missingType.parameter, return.phpDocType
             */
            #[Constructor]
            public static function publicMethod($parameterWithInvalidType): void {} // @phpstan-ignore class.notFound
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->methods->get('publicMethod')->returnType;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The return type `InvalidType` of method `.*` could not be resolved: .*$/', $type->message());
    }

    public function test_invalid_parameter_default_value_throws_exception(): void
    {
        $class = (new class () {
            /**
             * @param string $parameterWithInvalidDefaultValue
             */
            #[Constructor]
            public static function publicMethod($parameterWithInvalidDefaultValue = false): void {} // @phpstan-ignore parameter.defaultValue
        })::class;

        $class = $this->repository->for(new NativeClassType($class));
        $type = $class->methods->get('publicMethod')->parameters->get('parameterWithInvalidDefaultValue')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/Parameter `.*::publicMethod\(\$parameterWithInvalidDefaultValue\)` of type `string` has invalid default value false/', $type->message());
    }

    public function test_method_with_non_matching_return_types_throws_exception(): void
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

        $returnType = $this->repository
            ->for(new NativeClassType($class))
            ->methods
            ->get('publicMethod')
            ->returnType;

        self::assertInstanceOf(UnresolvableType::class, $returnType);
        self::assertMatchesRegularExpression('/^Return types for method `.*` do not match: `bool` \(docblock\) does not accept `string` \(native\).$/', $returnType->message());
    }

    public function test_method_that_should_not_be_included_is_not_included(): void
    {
        $class = (new class () {
            public function publicMethod(): void {}
        })::class;

        $methods = $this->repository
            ->for(new NativeClassType($class))
            ->methods;

        self::assertFalse($methods->has('publicMethod'));
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
        $this->expectExceptionMessage("The following type aliases already exist in class `$class`: `T`, `AnotherTemplate`.");

        $this->repository->for(new NativeClassType($class, ['T' => new FakeType(), 'AnotherTemplate' => new FakeType()]));
    }

    public function test_class_with_invalid_type_alias_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-type T = array{foo: string
             */
            (new class () {
                /** @var T */
                public $value; // @phpstan-ignore-line
            })::class;

        $type = $this->repository->for(new NativeClassType($class))->properties->get('value')->type;

        self::assertInstanceOf(UnresolvableType::class, $type);
        self::assertMatchesRegularExpression('/^The type `array{foo: string` for property `.*\$value` could not be resolved: The type `array{foo: string` for local alias `T` of the class `.*` could not be resolved: Missing closing curly bracket in shaped array signature `array{foo: string`\.$/', $type->message());
    }
}

abstract class AbstractClassWithPrivateConstructor
{
    private function __construct() {}
}

final class ClassWithInheritedPrivateConstructor extends AbstractClassWithPrivateConstructor {}
