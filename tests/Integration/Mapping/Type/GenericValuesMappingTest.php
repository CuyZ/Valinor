<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Type;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject as SimpleObjectAlias;

final class GenericValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'genericWithBoolean' => ['value' => true],
            'genericWithFloat' => ['value' => 42.404],
            'genericWithInteger' => ['value' => 1337],
            'genericWithString' => ['value' => 'foo'],
            'genericWithArrayOfStrings' => ['value' => ['foo', 'bar', 'baz']],
            'genericWithSimpleArrayOfStrings' => ['value' => ['foo', 'bar', 'baz']],
            'genericWithUnionOfScalar' => ['value' => 'foo'],
            'genericWithObject' => ['value' => ['value' => 'foo']],
            'genericWithObjectAlias' => ['value' => ['value' => 'foo']],
            'genericWithSpecifiedTypeWithString' => ['value' => 'foo'],
            'genericWithSpecifiedTypeWithObject' => ['value' => ['value' => 'foo']],
        ];

        foreach ([GenericValues::class, GenericValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(true, $result->genericWithBoolean->value);
            self::assertSame(42.404, $result->genericWithFloat->value);
            self::assertSame(1337, $result->genericWithInteger->value);
            self::assertSame('foo', $result->genericWithString->value);
            self::assertSame(['foo', 'bar', 'baz'], $result->genericWithArrayOfStrings->value);
            self::assertSame(['foo', 'bar', 'baz'], $result->genericWithSimpleArrayOfStrings->value);
            self::assertSame('foo', $result->genericWithUnionOfScalar->value);
            self::assertSame('foo', $result->genericWithObject->value->value);
            self::assertSame('foo', $result->genericWithObjectAlias->value->value);
            self::assertSame('foo', $result->genericWithSpecifiedTypeWithString->value);
            self::assertSame('foo', $result->genericWithSpecifiedTypeWithObject->value->value);
        }
    }
}

/**
 * @template T
 */
final class GenericObject
{
    /** @var T */
    public $value;
}

/**
 * @template T of string|object
 */
final class GenericObjectWithSpecifiedType
{
    /** @var T */
    public $value;
}

class GenericValues
{
    /** @var GenericObject<bool> */
    public GenericObject $genericWithBoolean;

    /** @var GenericObject<float> */
    public GenericObject $genericWithFloat;

    /** @var GenericObject<int> */
    public GenericObject $genericWithInteger;

    /** @var GenericObject<string> */
    public GenericObject $genericWithString;

    /** @var GenericObject<array<string>> */
    public GenericObject $genericWithArrayOfStrings;

    /** @var GenericObject<string[]> */
    public GenericObject $genericWithSimpleArrayOfStrings;

    /** @var GenericObject<bool|float|int|string> */
    public GenericObject $genericWithUnionOfScalar;

    /** @var GenericObject<SimpleObject> */
    public GenericObject $genericWithObject;

    /** @var GenericObject<SimpleObjectAlias> */
    public GenericObject $genericWithObjectAlias;

    /** @var GenericObjectWithSpecifiedType<string> */
    public GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithString;

    /** @var GenericObjectWithSpecifiedType<SimpleObject> */
    public GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithObject;
}

class GenericValuesWithConstructor extends GenericValues
{
    /**
     * @param GenericObject<bool> $genericWithBoolean
     * @param GenericObject<float> $genericWithFloat
     * @param GenericObject<int> $genericWithInteger
     * @param GenericObject<string> $genericWithString
     * @param GenericObject<array<string>> $genericWithArrayOfStrings
     * @param GenericObject<string[]> $genericWithSimpleArrayOfStrings
     * @param GenericObject<bool|float|int|string> $genericWithUnionOfScalar
     * @param GenericObject<SimpleObject> $genericWithObject
     * @param GenericObject<SimpleObjectAlias> $genericWithObjectAlias
     * @param GenericObjectWithSpecifiedType<string> $genericWithSpecifiedTypeWithString
     * @param GenericObjectWithSpecifiedType<SimpleObject> $genericWithSpecifiedTypeWithObject
     */
    public function __construct(
        GenericObject $genericWithBoolean,
        GenericObject $genericWithFloat,
        GenericObject $genericWithInteger,
        GenericObject $genericWithString,
        GenericObject $genericWithArrayOfStrings,
        GenericObject $genericWithSimpleArrayOfStrings,
        GenericObject $genericWithUnionOfScalar,
        GenericObject $genericWithObject,
        GenericObject $genericWithObjectAlias,
        GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithString,
        GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithObject
    ) {
        $this->genericWithBoolean = $genericWithBoolean;
        $this->genericWithFloat = $genericWithFloat;
        $this->genericWithInteger = $genericWithInteger;
        $this->genericWithString = $genericWithString;
        $this->genericWithArrayOfStrings = $genericWithArrayOfStrings;
        $this->genericWithSimpleArrayOfStrings = $genericWithSimpleArrayOfStrings;
        $this->genericWithUnionOfScalar = $genericWithUnionOfScalar;
        $this->genericWithObject = $genericWithObject;
        $this->genericWithObjectAlias = $genericWithObjectAlias;
        $this->genericWithSpecifiedTypeWithString = $genericWithSpecifiedTypeWithString;
        $this->genericWithSpecifiedTypeWithObject = $genericWithSpecifiedTypeWithObject;
    }
}
