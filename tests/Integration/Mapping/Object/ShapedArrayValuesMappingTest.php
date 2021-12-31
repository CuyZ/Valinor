<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use stdClass;

final class ShapedArrayValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'basicShapedArrayWithExcessiveKey' => [
                'foo' => 'foo',
                'bar' => 42,
            ],
            'basicShapedArrayWithStringKeys' => [
                'foo' => 'fiz',
                'bar' => 42,
            ],
            'basicShapedArrayWithIntegerKeys' => [
                0 => 'fiz',
                1 => 42.404,
            ],
            'shapedArrayWithObject' => [
                'foo' => ['value' => 'bar'],
            ],
            'shapedArrayWithOptionalValue' => [
                'optionalString' => 'some value',
            ],
            'shapedArrayOnSeveralLines' => [
                'foo' => 'fiz',
                'bar' => 42,
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

            self::assertSame(['foo' => 'foo'], $result->basicShapedArrayWithExcessiveKey);
            self::assertSame($source['basicShapedArrayWithStringKeys'], $result->basicShapedArrayWithStringKeys);
            self::assertSame($source['basicShapedArrayWithIntegerKeys'], $result->basicShapedArrayWithIntegerKeys);
            self::assertInstanceOf(SimpleObject::class, $result->shapedArrayWithObject['foo']);
            self::assertSame($source['shapedArrayWithOptionalValue'], $result->shapedArrayWithOptionalValue);
            self::assertSame($source['shapedArrayOnSeveralLines'], $result->shapedArrayOnSeveralLines);
            self::assertSame('bar', $result->advancedShapedArray['mandatoryString']);
            self::assertSame(1337, $result->advancedShapedArray[0]);
            self::assertSame(42.404, $result->advancedShapedArray[1]);
        }
    }

    public function test_value_that_cannot_be_casted_throws_exception(): void
    {
        try {
            $this->mapperBuilder->mapper()->map(ShapedArrayValues::class, [
                'basicShapedArrayWithStringKeys' => [
                    'foo' => new stdClass(),
                    'bar' => 42,
                ],
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['basicShapedArrayWithStringKeys']->children()['foo']->messages()[0];

            self::assertSame('1618736242', $error->code());
            self::assertSame('Cannot cast value of type `stdClass` to `string`.', (string)$error);
        }
    }
}

class ShapedArrayValues
{
    /** @var array{foo: string} */
    public array $basicShapedArrayWithExcessiveKey;

    /** @var array{foo: string, bar: int} */
    public array $basicShapedArrayWithStringKeys;

    /** @var array{0: string, 1: float} */
    public array $basicShapedArrayWithIntegerKeys;

    /** @var array{foo: SimpleObject} */
    public array $shapedArrayWithObject;

    /** @var array{optionalString?: string} */
    public array $shapedArrayWithOptionalValue;

    /**
     * @var array{
     *     foo: string,
     *     bar: int
     * }
     */
    public array $shapedArrayOnSeveralLines;

    /** @var array{0: int, float, optionalString?: string, mandatoryString: string} */
    public array $advancedShapedArray;
}

class ShapedArrayValuesWithConstructor extends ShapedArrayValues
{
    /**
     * @param array{foo: string} $basicShapedArrayWithExcessiveKey
     * @param array{foo: string, bar: int} $basicShapedArrayWithStringKeys
     * @param array{0: string, 1: float} $basicShapedArrayWithIntegerKeys
     * @param array{foo: SimpleObject} $shapedArrayWithObject
     * @param array{optionalString?: string} $shapedArrayWithOptionalValue
     * @param array{
     *     foo: string,
     *     bar: int
     * } $shapedArrayOnSeveralLines
     * @param array{0: int, float, optionalString?: string, mandatoryString: string} $advancedShapedArray
     */
    public function __construct(
        array $basicShapedArrayWithExcessiveKey,
        array $basicShapedArrayWithStringKeys,
        array $basicShapedArrayWithIntegerKeys,
        array $shapedArrayWithObject,
        array $shapedArrayWithOptionalValue,
        array $shapedArrayOnSeveralLines,
        array $advancedShapedArray
    ) {
        $this->basicShapedArrayWithExcessiveKey = $basicShapedArrayWithExcessiveKey;
        $this->basicShapedArrayWithStringKeys = $basicShapedArrayWithStringKeys;
        $this->basicShapedArrayWithIntegerKeys = $basicShapedArrayWithIntegerKeys;
        $this->shapedArrayWithObject = $shapedArrayWithObject;
        $this->shapedArrayWithOptionalValue = $shapedArrayWithOptionalValue;
        $this->shapedArrayOnSeveralLines = $shapedArrayOnSeveralLines;
        $this->advancedShapedArray = $advancedShapedArray;
    }
}
