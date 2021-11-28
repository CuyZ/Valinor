<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Type;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotCastToScalarValue;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use stdClass;

final class ShapedArrayValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'basicShapedArray' => [
                'foo' => 'bar',
            ],
            'shapedArrayWithObject' => [
                'foo' => ['value' => 'bar'],
            ],
            'shapedArrayWithOptionalValue' => [
                'optionalString' => 'some value',
            ],
            'advancedShapedArray' => [
                'mandatoryString' => 'bar',
                1337,
                '42.404',
            ],
        ];

        foreach ([ShapedArrayValues::class, ShapedArrayValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame($source['basicShapedArray'], $result->basicShapedArray);
            self::assertInstanceOf(SimpleObject::class, $result->shapedArrayWithObject['foo']);
            self::assertSame($source['shapedArrayWithOptionalValue'], $result->shapedArrayWithOptionalValue);
            self::assertSame('bar', $result->advancedShapedArray['mandatoryString']);
            self::assertSame(1337, $result->advancedShapedArray[0]);
            self::assertSame(42.404, $result->advancedShapedArray[1]);
        }
    }

    public function test_value_that_cannot_be_casted_throws_exception(): void
    {
        try {
            $this->mapperBuilder->mapper()->map(ShapedArrayValues::class, [
                'basicShapedArray' => ['foo' => new stdClass()],
                'shapedArrayWithOptionalValue' => [],
            ]);
        } catch (MappingError $exception) {
            $error = $exception->describe()['basicShapedArray.foo'][0];

            self::assertInstanceOf(CannotCastToScalarValue::class, $error);
            self::assertSame(1618736242, $error->getCode());
            self::assertSame('Cannot cast value of type `stdClass` to `string`.', $error->getMessage());
        }
    }
}

class ShapedArrayValues
{
    /** @var array{foo: string} */
    public array $basicShapedArray;

    /** @var array{foo: SimpleObject} */
    public array $shapedArrayWithObject;

    /** @var array{optionalString?: string} */
    public array $shapedArrayWithOptionalValue;

    /** @var array{int, float, optionalString?: string, mandatoryString: string} */
    public array $advancedShapedArray;
}

class ShapedArrayValuesWithConstructor extends ShapedArrayValues
{
    /**
     * @param array{foo: string} $basicShapedArray
     * @param array{foo: SimpleObject} $shapedArrayWithObject
     * @param array{optionalString?: string} $shapedArrayWithOptionalValue
     * @param array{int, float, optionalString?: string, mandatoryString: string} $advancedShapedArray
     */
    public function __construct(
        array $basicShapedArray,
        array $shapedArrayWithObject,
        array $shapedArrayWithOptionalValue,
        array $advancedShapedArray
    ) {
        $this->basicShapedArray = $basicShapedArray;
        $this->shapedArrayWithObject = $shapedArrayWithObject;
        $this->shapedArrayWithOptionalValue = $shapedArrayWithOptionalValue;
        $this->advancedShapedArray = $advancedShapedArray;
    }
}
