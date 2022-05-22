<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class SingleNodeMappingTest extends IntegrationTest
{
    public function test_single_property_and_constructor_parameter_are_mapped_properly(): void
    {
        $mapper = (new MapperBuilder())->mapper();

        // Note that the key `value` is missing from the source
        $scalarSource = 'foo';
        $arraySource = ['foo', 42.404, 1337];

        try {
            $singleScalarProperty = $mapper->map(SingleScalarProperty::class, $scalarSource);
            $singleConstructorScalarParameter = $mapper->map(SingleConstructorScalarParameter::class, $scalarSource);
            $singleArrayProperty = $mapper->map(SingleArrayProperty::class, $arraySource);
            $singleConstructorArrayParameter = $mapper->map(SingleConstructorArrayParameter::class, $arraySource);
            $singleScalarPropertyWithDefaultValue = $mapper->map(SingleScalarPropertyWithDefaultValue::class, null);
            $singleConstructorParameterWithDefaultValue = $mapper->map(SingleConstructorParameterWithDefaultValue::class, null);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $singleScalarProperty->value);
        self::assertSame('foo', $singleConstructorScalarParameter->value);
        self::assertSame(['foo', '42.404', '1337'], $singleArrayProperty->value);
        self::assertSame(['foo', '42.404', '1337'], $singleConstructorArrayParameter->value);
        self::assertSame('foo', $singleScalarPropertyWithDefaultValue->value);
        self::assertSame('bar', $singleConstructorParameterWithDefaultValue->value);
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

class SingleArrayProperty
{
    /** @var array<string> */
    public array $value = [];
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
    public function __construct(string $value = 'bar')
    {
        $this->value = $value;
    }
}
