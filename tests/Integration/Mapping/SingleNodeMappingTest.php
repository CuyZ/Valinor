<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class SingleNodeMappingTest extends IntegrationTest
{
    /**
     * @dataProvider single_property_and_constructor_parameter_data_provider
     *
     * @param class-string $className
     */
    public function test_single_property_and_constructor_parameter_are_mapped_properly(string $className, mixed $value): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map($className, $value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($value, $result->value); // @phpstan-ignore-line
    }

    /**
     * @dataProvider single_property_and_constructor_parameter_with_default_value_data_provider
     *
     * @param class-string $className
     */
    public function test_single_property_and_constructor_parameter_with_default_value_are_mapped_properly(string $className): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map($className, ['foo' => []]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']->value); // @phpstan-ignore-line
    }

    /**
     * @dataProvider single_property_and_constructor_parameter_data_provider
     *
     * @param class-string $className
     */
    public function test_single_property_and_constructor_parameter_can_be_mapped_with_array_with_property_name(string $className, mixed $value): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map($className, ['value' => $value]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($value, $result->value); // @phpstan-ignore-line
    }

    public function single_property_and_constructor_parameter_data_provider(): iterable
    {
        yield 'Single scalar property' => [
            SingleScalarProperty::class, 'foo',
        ];
        yield 'Single constructor scalar parameter' => [
            SingleConstructorScalarParameter::class, 'foo',
        ];
        yield 'Single nullable scalar property' => [
            SingleNullableScalarProperty::class, null,
        ];
        yield 'Single constructor nullable scalar property' => [
            SingleConstructorNullableScalarParameter::class, null,
        ];
        yield 'Single array property with empty array' => [
            SingleArrayProperty::class, [],
        ];
        yield 'Single array property with filled array' => [
            SingleArrayProperty::class, ['foo', '42.404', '1337'],
        ];
        yield 'Single array property with array containing entry with same key as property name' => [
            SingleArrayProperty::class, ['value' => 'foo', 'otherValue' => 'bar'],
        ];
        yield 'Single constructor array parameter with empty array' => [
            SingleConstructorArrayParameter::class, [],
        ];
        yield 'Single constructor array parameter with filled array' => [
            SingleConstructorArrayParameter::class, ['foo', '42.404', '1337'],
        ];
        yield 'Single constructor array parameter with array containing entry with same key as parameter name' => [
            SingleConstructorArrayParameter::class, ['value' => 'foo', 'otherValue' => 'bar'],
        ];
    }

    public function single_property_and_constructor_parameter_with_default_value_data_provider(): iterable
    {
        yield ['array{foo: ' . SingleScalarPropertyWithDefaultValue::class . '}'];
        yield ['array{foo: ' . SingleConstructorParameterWithDefaultValue::class . '}'];
    }
}

class SingleScalarProperty
{
    public string $value;
}

class SingleConstructorScalarParameter extends SingleScalarProperty
{
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}

class SingleNullableScalarProperty
{
    public ?string $value;
}

class SingleConstructorNullableScalarParameter extends SingleNullableScalarProperty
{
    public function __construct(?string $value)
    {
        $this->value = $value;
    }
}

class SingleArrayProperty
{
    /** @var array<string> */
    public array $value;
}

class SingleConstructorArrayParameter extends SingleArrayProperty
{
    /**
     * @param array<string> $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }
}

class SingleScalarPropertyWithDefaultValue
{
    public string $value = 'foo';
}

class SingleConstructorParameterWithDefaultValue extends SingleScalarPropertyWithDefaultValue
{
    public function __construct(string $value = 'foo')
    {
        $this->value = $value;
    }
}
