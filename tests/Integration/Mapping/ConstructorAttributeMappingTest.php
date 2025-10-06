<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodWithAttributeReturnType;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use stdClass;

final class ConstructorAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_map_class_with_constructor_method_with_attribute(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeClassWithConstructorAttribute::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value());
    }

    public function test_can_map_class_with_inherited_constructor_method_with_attribute(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeClassWithInheritedConstructorAttribute::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value());
    }

    public function test_can_map_class_with_attribute_on_native_constructor_and_static_factory(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(SomeClassWithConstructorAttributeOnNativeConstructorAndStaticFactory::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->valueA());
    }

    public function test_constructor_registered_both_with_attribute_and_explicit_registration_does_not_provoke_collision(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeClassWithConstructorAttribute::someConstructor(...))
                ->mapper()
                ->map(SomeClassWithConstructorAttribute::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value());
    }

    public function test_map_class_with_constructor_with_attribute_with_invalid_return_type_throws_exception(): void
    {
        $className = SomeClassWithConstructorAttributeWithInvalidReturnType::class;

        $this->expectException(InvalidConstructorMethodWithAttributeReturnType::class);
        $this->expectExceptionMessage("Invalid return type `string` for constructor `$className::someConstructor()`, it must be `$className`.");

        $this->mapperBuilder()
            ->mapper()
            ->map($className, 'foo');
    }

    public function test_map_class_with_constructor_with_attribute_with_invalid_return_class_name_throws_exception(): void
    {
        $className = SomeClassWithConstructorAttributeWithInvalidReturnClassName::class;

        $this->expectException(InvalidConstructorMethodWithAttributeReturnType::class);
        $this->expectExceptionMessage("Invalid return type `stdClass` for constructor `$className::someConstructor()`, it must be `$className`.");

        $this->mapperBuilder()
            ->mapper()
            ->map($className, 'foo');
    }

    public function test_map_class_with_constructor_with_attribute_with_unresolvable_return_type_throws_exception(): void
    {
        $className = SomeClassWithConstructorAttributeWithUnresolvableReturnClassName::class;

        $this->expectException(InvalidConstructorMethodWithAttributeReturnType::class);
        $this->expectExceptionMessage("The return type `Unresolvable-Type` of method `$className::someConstructor()` could not be resolved: Cannot parse unknown symbol `Unresolvable-Type`.");

        $this->mapperBuilder()
            ->mapper()
            ->map($className, 'foo');
    }
}

class SomeClassWithConstructorAttribute
{
    final private function __construct(private string $value) {}

    public function value(): string
    {
        return $this->value;
    }

    public static function someUnrelatedStaticMethod(): void {}

    #[Constructor]
    public static function someConstructor(string $value): self
    {
        return new static($value);
    }
}

final class SomeClassWithInheritedConstructorAttribute extends SomeClassWithConstructorAttribute {}

final class SomeClassWithConstructorAttributeOnNativeConstructorAndStaticFactory
{
    private string $valueA;

    private string $valueB;

    #[Constructor]
    public function __construct(string $value)
    {
        $this->valueA = $value;
        $this->valueB = 'default';
    }

    #[Constructor]
    public static function from(string $valueA, string $valueB): self
    {
        $instance = new self($valueA);
        $instance->valueB = $valueB;

        return $instance;
    }

    public function valueA(): string
    {
        return $this->valueA;
    }

    public function valueB(): string
    {
        return $this->valueB;
    }
}

final class SomeClassWithConstructorAttributeWithInvalidReturnType
{
    #[Constructor]
    public static function someConstructor(): string
    {
        return 'foo';
    }
}

final class SomeClassWithConstructorAttributeWithInvalidReturnClassName
{
    #[Constructor]
    public static function someConstructor(): stdClass
    {
        return new stdClass();
    }
}

final class SomeClassWithConstructorAttributeWithUnresolvableReturnClassName
{
    /**
     * @return Unresolvable-Type
     */
    // @phpstan-ignore return.unresolvableType, missingType.return
    #[Constructor]
    public static function someConstructor() {}
}
