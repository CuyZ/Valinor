<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\CircularDependencyDetected;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject as SimpleObjectAlias;

final class GenericValuesMappingTest extends IntegrationTestCase
{
    public function test_covariant_template_values_are_mapped_properly(): void
    {
        foreach ([
            CovariantGenericObject::class,
            PhpstanCovariantGenericObject::class,
            PsalmCovariantGenericObject::class,
        ] as $class) {
            try {
                /** @var CovariantGenericObject<string> $result */
                $result = $this->mapperBuilder()->mapper()->map("$class<string>", 'foo');
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame('foo', $result->value);
        }
    }

    public function test_covariant_template_with_constraint_values_are_mapped_properly(): void
    {
        try {
            /** @var CovariantGenericObjectWithConstraint<string> $result */
            $result = $this->mapperBuilder()->mapper()->map(CovariantGenericObjectWithConstraint::class . '<string>', 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value);
    }

    public function test_template_default_is_used_when_generic_is_omitted(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map(ObjectWithDefaultTemplate::class, ['value' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value);
    }

    public function test_template_default_with_bound_is_used_when_generic_is_omitted(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map(ObjectWithBoundDefaultTemplate::class, ['value' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $result->value);
    }

    public function test_assigned_generic_takes_precedence_over_template_default(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map(ObjectWithDefaultTemplate::class . '<int>', ['value' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $result->value);
    }

    public function test_self_referencing_object_with_template_default_is_detected_as_circular(): void
    {
        $this->expectException(CircularDependencyDetected::class);
        $this->expectExceptionMessage('Circular dependency detected for `' . ObjectWithDefaultTemplateAndSelfReference::class . '::$value`.');

        $this->mapperBuilder()->mapper()->map(ObjectWithDefaultTemplateAndSelfReference::class, []);
    }

    public function test_self_reference_omitting_only_the_defaulted_generic_is_detected_as_circular(): void
    {
        $this->expectException(CircularDependencyDetected::class);
        $this->expectExceptionMessage('Circular dependency detected for `' . ObjectWithTrailingDefaultAndSelfReference::class . '::$value`.');

        $this->mapperBuilder()->mapper()->map(ObjectWithTrailingDefaultAndSelfReference::class . '<int>', []);
    }

    public function test_object_nested_in_its_own_generic_is_not_detected_as_circular(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map(
                ObjectWithSingleGeneric::class . '<' . ObjectWithSingleGeneric::class . '<string>>',
                ['value' => ['value' => 'foo']],
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value->value);
    }

    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'genericWithBoolean' => true,
            'genericWithFloat' => 42.404,
            'genericWithInteger' => 1337,
            'genericWithIntegerValue' => 1337,
            'genericWithString' => 'foo',
            'genericWithSingleQuoteStringValue' => 'foo',
            'genericWithDoubleQuoteStringValue' => 'foo',
            'genericWithArrayOfStrings' => ['foo', 'bar', 'baz'],
            'genericWithSimpleArrayOfStrings' => ['foo', 'bar', 'baz'],
            'genericWithUnionOfScalar' => 'foo',
            'genericWithObject' => 'foo',
            'genericWithObjectAlias' => 'foo',
            'genericWithTwoTemplates' => [
                'valueA' => 'foo',
                'valueB' => 42,
            ],
            'genericWithTwoTemplatesOnSeveralLines' => [
                'valueA' => 'foo',
                'valueB' => 42,
            ],
            'genericWithSpecifiedTypeWithString' => 'foo',
            'genericWithSpecifiedTypeWithObject' => 'foo',
            'genericWithSpecifiedTypeWithIntegerValue' => 42,
            'genericWithSpecifiedTypeWithStringValue' => 'foo',
        ];

        foreach ([GenericValues::class, GenericValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder()->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(true, $result->genericWithBoolean->value);
            self::assertSame(42.404, $result->genericWithFloat->value);
            self::assertSame(1337, $result->genericWithInteger->value);
            self::assertSame(1337, $result->genericWithIntegerValue->value); // @phpstan-ignore-line
            self::assertSame('foo', $result->genericWithString->value);
            self::assertSame('foo', $result->genericWithSingleQuoteStringValue->value); // @phpstan-ignore-line
            self::assertSame('foo', $result->genericWithDoubleQuoteStringValue->value); // @phpstan-ignore-line
            self::assertSame(['foo', 'bar', 'baz'], $result->genericWithArrayOfStrings->value);
            self::assertSame(['foo', 'bar', 'baz'], $result->genericWithSimpleArrayOfStrings->value);
            self::assertSame('foo', $result->genericWithUnionOfScalar->value);
            self::assertSame('foo', $result->genericWithObject->value->value);
            self::assertSame('foo', $result->genericWithObjectAlias->value->value);
            self::assertSame('foo', $result->genericWithTwoTemplates->valueA);
            self::assertSame(42, $result->genericWithTwoTemplates->valueB);
            self::assertSame('foo', $result->genericWithTwoTemplatesOnSeveralLines->valueA);
            self::assertSame(42, $result->genericWithTwoTemplatesOnSeveralLines->valueB);
            self::assertSame('foo', $result->genericWithSpecifiedTypeWithString->value);
            self::assertSame('foo', $result->genericWithSpecifiedTypeWithObject->value->value);
            self::assertSame(42, $result->genericWithSpecifiedTypeWithIntegerValue->value); // @phpstan-ignore-line
            self::assertSame('foo', $result->genericWithSpecifiedTypeWithStringValue->value); // @phpstan-ignore-line
        }
    }
}

/**
 * @template T
 */
final class GenericObjectWithOneTemplate
{
    /** @var T */
    public $value;
}

/**
 * @template TemplateA
 * @template TemplateB
 */
final class GenericObjectWithTwoTemplates
{
    /** @var TemplateA */
    public $valueA;

    /** @var TemplateB */
    public $valueB;
}

/**
 * @template T of string|object|42|'foo'
 */
final class GenericObjectWithSpecifiedType
{
    /** @var T */
    public $value;
}

class GenericValues
{
    /** @var GenericObjectWithOneTemplate<bool> */
    public GenericObjectWithOneTemplate $genericWithBoolean;

    /** @var GenericObjectWithOneTemplate<float> */
    public GenericObjectWithOneTemplate $genericWithFloat;

    /** @var GenericObjectWithOneTemplate<int> */
    public GenericObjectWithOneTemplate $genericWithInteger;

    /** @var GenericObjectWithOneTemplate<1337> */
    public GenericObjectWithOneTemplate $genericWithIntegerValue;

    /** @var GenericObjectWithOneTemplate<string> */
    public GenericObjectWithOneTemplate $genericWithString;

    /** @var GenericObjectWithOneTemplate<'foo'> */
    public GenericObjectWithOneTemplate $genericWithSingleQuoteStringValue;

    /** @var GenericObjectWithOneTemplate<"foo"> */
    public GenericObjectWithOneTemplate $genericWithDoubleQuoteStringValue;

    /** @var GenericObjectWithOneTemplate<array<string>> */
    public GenericObjectWithOneTemplate $genericWithArrayOfStrings;

    /** @var GenericObjectWithOneTemplate<string[]> */
    public GenericObjectWithOneTemplate $genericWithSimpleArrayOfStrings;

    /** @var GenericObjectWithOneTemplate<bool|float|int|string> */
    public GenericObjectWithOneTemplate $genericWithUnionOfScalar;

    /** @var GenericObjectWithOneTemplate<SimpleObject> */
    public GenericObjectWithOneTemplate $genericWithObject;

    /** @var GenericObjectWithOneTemplate<SimpleObjectAlias> */
    public GenericObjectWithOneTemplate $genericWithObjectAlias;

    /** @var GenericObjectWithTwoTemplates<string, int> */
    public GenericObjectWithTwoTemplates $genericWithTwoTemplates;

    /**
     * @var GenericObjectWithTwoTemplates<
     *     string,
     *     int
     * >
     */
    public GenericObjectWithTwoTemplates $genericWithTwoTemplatesOnSeveralLines;

    /** @var GenericObjectWithSpecifiedType<string> */
    public GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithString;

    /** @var GenericObjectWithSpecifiedType<SimpleObject> */
    public GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithObject;

    /** @var GenericObjectWithSpecifiedType<42> */
    public GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithIntegerValue;

    /** @var GenericObjectWithSpecifiedType<'foo'> */
    public GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithStringValue;
}

class GenericValuesWithConstructor extends GenericValues
{
    /**
     * @param GenericObjectWithOneTemplate<bool> $genericWithBoolean
     * @param GenericObjectWithOneTemplate<float> $genericWithFloat
     * @param GenericObjectWithOneTemplate<int> $genericWithInteger
     * @param GenericObjectWithOneTemplate<1337> $genericWithIntegerValue
     * @param GenericObjectWithOneTemplate<string> $genericWithString
     * @param GenericObjectWithOneTemplate<'foo'> $genericWithSingleQuoteStringValue
     * @param GenericObjectWithOneTemplate<"foo"> $genericWithDoubleQuoteStringValue
     * @param GenericObjectWithOneTemplate<array<string>> $genericWithArrayOfStrings
     * @param GenericObjectWithOneTemplate<string[]> $genericWithSimpleArrayOfStrings
     * @param GenericObjectWithOneTemplate<bool|float|int|string> $genericWithUnionOfScalar
     * @param GenericObjectWithOneTemplate<SimpleObject> $genericWithObject
     * @param GenericObjectWithOneTemplate<SimpleObjectAlias> $genericWithObjectAlias
     * @param GenericObjectWithTwoTemplates<string, int> $genericWithTwoTemplates
     * @param GenericObjectWithTwoTemplates<
     *     string,
     *     int
     * > $genericWithTwoTemplatesOnSeveralLines
     * @param GenericObjectWithSpecifiedType<string> $genericWithSpecifiedTypeWithString
     * @param GenericObjectWithSpecifiedType<SimpleObject> $genericWithSpecifiedTypeWithObject
     * @param GenericObjectWithSpecifiedType<42> $genericWithSpecifiedTypeWithIntegerValue
     * @param GenericObjectWithSpecifiedType<'foo'> $genericWithSpecifiedTypeWithStringValue
     */
    public function __construct(
        GenericObjectWithOneTemplate $genericWithBoolean,
        GenericObjectWithOneTemplate $genericWithFloat,
        GenericObjectWithOneTemplate $genericWithInteger,
        GenericObjectWithOneTemplate $genericWithIntegerValue,
        GenericObjectWithOneTemplate $genericWithString,
        GenericObjectWithOneTemplate $genericWithSingleQuoteStringValue,
        GenericObjectWithOneTemplate $genericWithDoubleQuoteStringValue,
        GenericObjectWithOneTemplate $genericWithArrayOfStrings,
        GenericObjectWithOneTemplate $genericWithSimpleArrayOfStrings,
        GenericObjectWithOneTemplate $genericWithUnionOfScalar,
        GenericObjectWithOneTemplate $genericWithObject,
        GenericObjectWithOneTemplate $genericWithObjectAlias,
        GenericObjectWithTwoTemplates $genericWithTwoTemplates,
        GenericObjectWithTwoTemplates $genericWithTwoTemplatesOnSeveralLines,
        GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithString,
        GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithObject,
        GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithIntegerValue,
        GenericObjectWithSpecifiedType $genericWithSpecifiedTypeWithStringValue
    ) {
        $this->genericWithBoolean = $genericWithBoolean;
        $this->genericWithFloat = $genericWithFloat;
        $this->genericWithInteger = $genericWithInteger;
        $this->genericWithIntegerValue = $genericWithIntegerValue;
        $this->genericWithString = $genericWithString;
        $this->genericWithSingleQuoteStringValue = $genericWithSingleQuoteStringValue;
        $this->genericWithDoubleQuoteStringValue = $genericWithDoubleQuoteStringValue;
        $this->genericWithArrayOfStrings = $genericWithArrayOfStrings;
        $this->genericWithSimpleArrayOfStrings = $genericWithSimpleArrayOfStrings;
        $this->genericWithUnionOfScalar = $genericWithUnionOfScalar;
        $this->genericWithObject = $genericWithObject;
        $this->genericWithObjectAlias = $genericWithObjectAlias;
        $this->genericWithTwoTemplates = $genericWithTwoTemplates;
        $this->genericWithTwoTemplatesOnSeveralLines = $genericWithTwoTemplatesOnSeveralLines;
        $this->genericWithSpecifiedTypeWithString = $genericWithSpecifiedTypeWithString;
        $this->genericWithSpecifiedTypeWithObject = $genericWithSpecifiedTypeWithObject;
        $this->genericWithSpecifiedTypeWithIntegerValue = $genericWithSpecifiedTypeWithIntegerValue;
        $this->genericWithSpecifiedTypeWithStringValue = $genericWithSpecifiedTypeWithStringValue;
    }
}

/**
 * @template-covariant T
 */
final readonly class CovariantGenericObject
{
    /** @param T $value */
    public function __construct(
        public mixed $value,
    ) {}
}

/**
 * @phpstan-template-covariant T
 */
final readonly class PhpstanCovariantGenericObject
{
    /** @param T $value */
    public function __construct(
        public mixed $value,
    ) {}
}

/**
 * @psalm-template-covariant T
 */
final readonly class PsalmCovariantGenericObject
{
    /** @param T $value */
    public function __construct(
        public mixed $value,
    ) {}
}

/**
 * @template-covariant T of string|int
 */
final readonly class CovariantGenericObjectWithConstraint
{
    /** @param T $value */
    public function __construct(
        public mixed $value,
    ) {}
}

/**
 * @template T = string
 */
final class ObjectWithDefaultTemplate
{
    /** @var T */
    public mixed $value;
}

/**
 * @template T of int|string = int
 */
final class ObjectWithBoundDefaultTemplate
{
    /** @var T */
    public mixed $value;
}

/**
 * @template T = string
 */
final class ObjectWithDefaultTemplateAndSelfReference
{
    public self $value;
}

/**
 * @template T
 */
final class ObjectWithSingleGeneric
{
    /** @var T */
    public mixed $value;
}

/**
 * @template TemplateA
 * @template TemplateB = string
 */
final class ObjectWithTrailingDefaultAndSelfReference
{
    /** @var self<TemplateA> */
    public self $value;
}
