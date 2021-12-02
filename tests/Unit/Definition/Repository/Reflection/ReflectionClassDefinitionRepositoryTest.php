<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Exception\InvalidParameterDefaultValue;
use CuyZ\Valinor\Definition\Exception\InvalidPropertyDefaultValue;
use CuyZ\Valinor\Definition\Exception\TypesDoNotMatch;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeAttributesRepository;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Factory\FakeTypeParserFactory;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Types\BooleanType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\TestCase;

use function get_class;

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

        $signature = new ClassSignature(get_class($object));
        $properties = $this->repository->for($signature)->properties();

        self::assertTrue($properties->get('propertyWithDefaultValue')->hasDefaultValue());
        self::assertSame('Default value for property', $properties->get('propertyWithDefaultValue')->defaultValue());

        self::assertInstanceOf(BooleanType::class, $properties->get('propertyWithDocBlockType')->type());

        self::assertInstanceOf(MixedType::class, $properties->get('propertyWithNoType')->type());

        self::assertInstanceOf(StringType::class, $properties->get('publicProperty')->type());
        self::assertTrue($properties->get('publicProperty')->isPublic());

        self::assertInstanceOf(StringType::class, $properties->get('protectedProperty')->type());
        self::assertFalse($properties->get('protectedProperty')->isPublic());

        self::assertInstanceOf(BooleanType::class, $properties->get('privateProperty')->type());
        self::assertFalse($properties->get('privateProperty')->isPublic());
    }

    public function test_methods_can_be_retrieved(): void
    {
        $object = new class () {
            public function __construct()
            {
            }

            /**
             * @param string $parameterWithDocBlockType
             * @phpstan-ignore-next-line
             */
            public function publicMethod(
                bool $mandatoryParameter,
                $parameterWithNoType,
                $parameterWithDocBlockType,
                string $optionalParameter = 'Optional parameter value'
            ): void {
            }

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

        $className = get_class($object);
        $signature = new ClassSignature($className);
        $methods = $this->repository->for($signature)->methods();

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

        self::assertInstanceOf(BooleanType::class, $mandatoryParameter->type());
        self::assertFalse($mandatoryParameter->isOptional());

        self::assertInstanceOf(MixedType::class, $parameterWithNoType->type());

        self::assertInstanceOf(StringType::class, $parameterWithDocBlockType->type());

        self::assertTrue($parameters->get('optionalParameter')->isOptional());
        self::assertSame('Optional parameter value', $optionalParameter->defaultValue());
    }

    public function test_invalid_property_type_throws_exception(): void
    {
        $class = get_class(new class () {
            /** @var InvalidType */
            public $propertyWithInvalidType; // @phpstan-ignore-line
        });

        $class = $this->repository->for(new ClassSignature($class));
        $type = $class->properties()->get('propertyWithInvalidType')->type();

        self::assertInstanceOf(UnresolvableType::class, $type);
        /** @var UnresolvableType $type */
        self::assertMatchesRegularExpression('/^The type `InvalidType` for property `.*` could not be resolved: .*$/', $type->getMessage());
    }

    public function test_invalid_property_default_value_throws_exception(): void
    {
        $class = get_class(new class () {
            /** @var string */
            public $propertyWithInvalidDefaultValue = false; // @phpstan-ignore-line
        });

        $this->expectException(InvalidPropertyDefaultValue::class);
        $this->expectExceptionCode(1629211093);
        $this->expectExceptionMessage("Default value of property `$class::\$propertyWithInvalidDefaultValue` is not accepted by `string`.");

        $this->repository->for(new ClassSignature($class));
    }

    public function test_property_with_non_matching_types_throws_exception(): void
    {
        $class = get_class(new class () {
            /**
             * @phpstan-ignore-next-line
             * @var string
             */
            public bool $propertyWithNotMatchingTypes;
        });

        $this->expectException(TypesDoNotMatch::class);
        $this->expectExceptionCode(1638471381);
        $this->expectExceptionMessage("Types for property `$class::\$propertyWithNotMatchingTypes` do not match: `string` (docblock) does not accept `bool` (native).");

        $this->repository->for(new ClassSignature($class));
    }

    public function test_invalid_parameter_type_throws_exception(): void
    {
        $class = get_class(new class () {
            /**
             * @formatter:off
             * @param InvalidTypeWithPendingSpaces         $parameterWithInvalidType
             * @formatter:on
             * @phpstan-ignore-next-line
             */
            public function publicMethod($parameterWithInvalidType): void
            {
            }
        });

        $class = $this->repository->for(new ClassSignature($class));
        $type = $class->methods()->get('publicMethod')->parameters()->get('parameterWithInvalidType')->type();

        self::assertInstanceOf(UnresolvableType::class, $type);
        /** @var UnresolvableType $type */
        self::assertMatchesRegularExpression('/^The type `InvalidTypeWithPendingSpaces` for parameter `.*` could not be resolved: .*$/', $type->getMessage());
    }

    public function test_invalid_parameter_default_value_throws_exception(): void
    {
        $class = get_class(new class () {
            /**
             * @param string $parameterWithInvalidDefaultValue
             * @phpstan-ignore-next-line
             */
            public function publicMethod($parameterWithInvalidDefaultValue = false): void
            {
            }
        });

        $this->expectException(InvalidParameterDefaultValue::class);
        $this->expectExceptionCode(1629210903);
        $this->expectExceptionMessage("Default value of parameter `$class::publicMethod(\$parameterWithInvalidDefaultValue)` is not accepted by `string`.");

        $this->repository->for(new ClassSignature($class));
    }

    public function test_parameter_with_non_matching_types_throws_exception(): void
    {
        $class = get_class(new class () {
            /**
             * @param string $parameterWithNotMatchingTypes
             * @phpstan-ignore-next-line
             */
            public function publicMethod(bool $parameterWithNotMatchingTypes): void
            {
            }
        });

        $this->expectException(TypesDoNotMatch::class);
        $this->expectExceptionCode(1638471381);
        $this->expectExceptionMessage("Types for parameter `$class::publicMethod(\$parameterWithNotMatchingTypes)` do not match: `string` (docblock) does not accept `bool` (native).");

        $this->repository->for(new ClassSignature($class));
    }

    public function test_method_with_non_matching_return_types_throws_exception(): void
    {
        $class = get_class(new class () {
            /**
             * @return bool
             * @phpstan-ignore-next-line
             */
            public function publicMethod(): string
            {
                return 'foo';
            }
        });

        $this->expectException(TypesDoNotMatch::class);
        $this->expectExceptionCode(1638471381);
        $this->expectExceptionMessage("Return types for method `$class::publicMethod()` do not match: `bool` (docblock) does not accept `string` (native).");

        $this->repository->for(new ClassSignature($class));
    }
}
