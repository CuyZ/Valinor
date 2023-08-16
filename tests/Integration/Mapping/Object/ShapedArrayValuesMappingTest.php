<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use stdClass;

final class ShapedArrayValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'basicShapedArrayWithStringKeys' => [
                'foo' => 'fiz',
                'bar' => 42,
            ],
            'basicShapedArrayWithSingleQuotedStringKeys' => [
                'foo' => 'fiz',
                'bar fiz' => 42,
                'fiz & $ § % fiz' => 42.404,
            ],
            'basicShapedArrayWithDoubleQuotedStringKeys' => [
                'foo' => 'fiz',
                'bar fiz' => 42,
                'fiz & $ § % fiz' => 42.404,
            ],
            'basicShapedArrayWithIntegerKeys' => [
                0 => 'fiz',
                1 => 42.404,
            ],
            'shapedArrayWithObject' => ['foo' => 'bar'],
            'shapedArrayWithOptionalValue' => [
                'optionalString' => 'some value',
            ],
            'shapedArrayOnSeveralLines' => [
                'foo' => 'fiz',
                'bar' => 42,
            ],
            'shapedArrayOnSeveralLinesWithTrailingComma' => [
                'foo' => 'fiz',
                'bar' => 42,
            ],
            'advancedShapedArray' => [
                'mandatoryString' => 'bar',
                1337,
                42.404,
            ],
            'shapedArrayWithClassNameAsKey' => [
                'stdClass' => 'foo',
            ],
            'shapedArrayWithLowercaseClassNameAsKey' => [
                'stdclass' => 'foo',
            ],
            'shapedArrayWithEnumNameAsKey' => [
                'EnumAtRootNamespace' => 'foo',
            ],
            'shapedArrayWithLowercaseEnumNameAsKey' => [
                'enumatrootnamespace' => 'foo',
            ],
        ];

        foreach ([ShapedArrayValues::class, ShapedArrayValuesWithConstructor::class] as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame($source['basicShapedArrayWithStringKeys'], $result->basicShapedArrayWithStringKeys);
            self::assertSame($source['basicShapedArrayWithSingleQuotedStringKeys'], $result->basicShapedArrayWithSingleQuotedStringKeys);
            self::assertSame($source['basicShapedArrayWithDoubleQuotedStringKeys'], $result->basicShapedArrayWithDoubleQuotedStringKeys);
            self::assertSame($source['basicShapedArrayWithIntegerKeys'], $result->basicShapedArrayWithIntegerKeys);
            self::assertInstanceOf(SimpleObject::class, $result->shapedArrayWithObject['foo']); // @phpstan-ignore-line
            self::assertSame($source['shapedArrayWithOptionalValue'], $result->shapedArrayWithOptionalValue);
            self::assertSame($source['shapedArrayOnSeveralLines'], $result->shapedArrayOnSeveralLines);
            self::assertSame($source['shapedArrayOnSeveralLinesWithTrailingComma'], $result->shapedArrayOnSeveralLinesWithTrailingComma);
            self::assertSame('bar', $result->advancedShapedArray['mandatoryString']);
            self::assertSame(1337, $result->advancedShapedArray[0]);
            self::assertSame(42.404, $result->advancedShapedArray[1]);
            self::assertSame('foo', $result->shapedArrayWithClassNameAsKey['stdClass']);
            self::assertSame('foo', $result->shapedArrayWithLowercaseClassNameAsKey['stdclass']);
            self::assertSame('foo', $result->shapedArrayWithEnumNameAsKey['EnumAtRootNamespace']);
            self::assertSame('foo', $result->shapedArrayWithLowercaseEnumNameAsKey['enumatrootnamespace']);
        }
    }

    public function test_value_with_invalid_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(ShapedArrayValues::class, [
                'basicShapedArrayWithStringKeys' => [
                    'foo' => new stdClass(),
                    'bar' => 42,
                ],
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['basicShapedArrayWithStringKeys']->children()['foo']->messages()[0];

            self::assertSame('Value object(stdClass) is not a valid string.', (string)$error);
        }
    }
}

class ShapedArrayValues
{
    /** @var array{foo: string, bar: int} */
    public array $basicShapedArrayWithStringKeys;

    /** @var array{'foo': string, 'bar fiz': int, 'fiz & $ § % fiz': float} */
    public array $basicShapedArrayWithSingleQuotedStringKeys;

    /** @var array{"foo": string, "bar fiz": int, "fiz & $ § % fiz": float} */
    public array $basicShapedArrayWithDoubleQuotedStringKeys;

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

    /**
     * @var array{
     *     foo: string,
     *     bar: int,
     * }
     */
    public array $shapedArrayOnSeveralLinesWithTrailingComma;

    /** @var array{0: int, float, optionalString?: string, mandatoryString: string} */
    public array $advancedShapedArray;

    /** @var array{stdClass: string} */
    public array $shapedArrayWithClassNameAsKey;

    /** @var array{stdclass: string} */
    public array $shapedArrayWithLowercaseClassNameAsKey;

    /** @var array{EnumAtRootNamespace: string} */
    public array $shapedArrayWithEnumNameAsKey;

    /** @var array{enumatrootnamespace: string} */
    public array $shapedArrayWithLowercaseEnumNameAsKey;
}

class ShapedArrayValuesWithConstructor extends ShapedArrayValues
{
    /**
     * @param array{foo: string, bar: int} $basicShapedArrayWithStringKeys
     * @param array{'foo': string, 'bar fiz': int, 'fiz & $ § % fiz': float} $basicShapedArrayWithSingleQuotedStringKeys
     * @param array{"foo": string, "bar fiz": int, "fiz & $ § % fiz": float} $basicShapedArrayWithDoubleQuotedStringKeys
     * @param array{0: string, 1: float} $basicShapedArrayWithIntegerKeys
     * @param array{foo: SimpleObject} $shapedArrayWithObject
     * @param array{optionalString?: string} $shapedArrayWithOptionalValue
     * @param array{
     *     foo: string,
     *     bar: int
     * } $shapedArrayOnSeveralLines
     * @param array{
     *     foo: string,
     *     bar: int,
     * } $shapedArrayOnSeveralLinesWithTrailingComma
     * @param array{0: int, float, optionalString?: string, mandatoryString: string} $advancedShapedArray
     * @param array{stdClass: string} $shapedArrayWithClassNameAsKey
     * @param array{stdclass: string} $shapedArrayWithLowercaseClassNameAsKey
     * @param array{EnumAtRootNamespace: string} $shapedArrayWithEnumNameAsKey
     * @param array{enumatrootnamespace: string} $shapedArrayWithLowercaseEnumNameAsKey
     */
    public function __construct(
        array $basicShapedArrayWithStringKeys,
        array $basicShapedArrayWithSingleQuotedStringKeys,
        array $basicShapedArrayWithDoubleQuotedStringKeys,
        array $basicShapedArrayWithIntegerKeys,
        array $shapedArrayWithObject,
        array $shapedArrayWithOptionalValue,
        array $shapedArrayOnSeveralLines,
        array $shapedArrayOnSeveralLinesWithTrailingComma,
        array $advancedShapedArray,
        array $shapedArrayWithClassNameAsKey,
        array $shapedArrayWithLowercaseClassNameAsKey,
        array $shapedArrayWithEnumNameAsKey,
        array $shapedArrayWithLowercaseEnumNameAsKey,
    ) {
        $this->basicShapedArrayWithStringKeys = $basicShapedArrayWithStringKeys;
        $this->basicShapedArrayWithSingleQuotedStringKeys = $basicShapedArrayWithSingleQuotedStringKeys;
        $this->basicShapedArrayWithDoubleQuotedStringKeys = $basicShapedArrayWithDoubleQuotedStringKeys;
        $this->basicShapedArrayWithIntegerKeys = $basicShapedArrayWithIntegerKeys;
        $this->shapedArrayWithObject = $shapedArrayWithObject;
        $this->shapedArrayWithOptionalValue = $shapedArrayWithOptionalValue;
        $this->shapedArrayOnSeveralLines = $shapedArrayOnSeveralLines;
        $this->shapedArrayOnSeveralLinesWithTrailingComma = $shapedArrayOnSeveralLinesWithTrailingComma;
        $this->advancedShapedArray = $advancedShapedArray;
        $this->shapedArrayWithClassNameAsKey = $shapedArrayWithClassNameAsKey;
        $this->shapedArrayWithLowercaseClassNameAsKey = $shapedArrayWithLowercaseClassNameAsKey;
        $this->shapedArrayWithEnumNameAsKey = $shapedArrayWithEnumNameAsKey;
        $this->shapedArrayWithLowercaseEnumNameAsKey = $shapedArrayWithLowercaseEnumNameAsKey;
    }
}
