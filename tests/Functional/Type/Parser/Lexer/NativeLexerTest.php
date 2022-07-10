<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fixture\Object\AbstractObject;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidIntersectionType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidArrayKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidIterableKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\IterableClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\IterableCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ListClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayColonTokenMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayEmptyElements;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\SimpleArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\RightIntersectionTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\RightUnionTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\ClassStringClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeInvalidMaxValue;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeInvalidMinValue;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingClosingBracket;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingComma;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingMaxValue;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingMinValue;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\InvalidClassStringSubType;
use CuyZ\Valinor\Type\Parser\Exception\UnknownSymbol;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\LexingParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\FloatValueType;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NativeLexerTest extends TestCase
{
    private TypeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $lexer = new NativeLexer();

        $this->parser = new LexingParser($lexer);
    }

    /**
     * @dataProvider parse_valid_types_returns_valid_result_data_provider
     *
     * @param class-string<Type> $type
     */
    public function test_parse_valid_types_returns_valid_result(string $raw, string $transformed, string $type): void
    {
        $result = $this->parser->parse($raw);

        self::assertSame($transformed, $result->toString());
        self::assertInstanceOf($type, $result);
    }

    public function parse_valid_types_returns_valid_result_data_provider(): array
    {
        return [
            'Null type' => [
                'raw' => 'null',
                'transformed' => 'null',
                'type' => NullType::class,
            ],
            'Null type - uppercase' => [
                'raw' => 'NULL',
                'transformed' => 'null',
                'type' => NullType::class,
            ],
            'Null type followed by description' => [
                'raw' => 'null lorem ipsum',
                'transformed' => 'null',
                'type' => NullType::class,
            ],
            'True type' => [
                'raw' => 'true',
                'transformed' => 'true',
                'type' => BooleanValueType::class,
            ],
            'True type - uppercase' => [
                'raw' => 'TRUE',
                'transformed' => 'true',
                'type' => BooleanValueType::class,
            ],
            'False type' => [
                'raw' => 'false',
                'transformed' => 'false',
                'type' => BooleanValueType::class,
            ],
            'False type - uppercase' => [
                'raw' => 'FALSE',
                'transformed' => 'false',
                'type' => BooleanValueType::class,
            ],
            'Mixed type' => [
                'raw' => 'mixed',
                'transformed' => 'mixed',
                'type' => MixedType::class,
            ],
            'Mixed type - uppercase' => [
                'raw' => 'MIXED',
                'transformed' => 'mixed',
                'type' => MixedType::class,
            ],
            'Mixed type followed by description' => [
                'raw' => 'mixed lorem ipsum',
                'transformed' => 'mixed',
                'type' => MixedType::class,
            ],
            'Float type' => [
                'raw' => 'float',
                'transformed' => 'float',
                'type' => NativeFloatType::class,
            ],
            'Float type - uppercase' => [
                'raw' => 'FLOAT',
                'transformed' => 'float',
                'type' => NativeFloatType::class,
            ],
            'Float type followed by description' => [
                'raw' => 'float lorem ipsum',
                'transformed' => 'float',
                'type' => NativeFloatType::class,
            ],
            'Positive float value' => [
                'raw' => '1337.42',
                'transformed' => '1337.42',
                'type' => FloatValueType::class,
            ],
            'Positive float value followed by description' => [
                'raw' => '1337.42 lorem ipsum',
                'transformed' => '1337.42',
                'type' => FloatValueType::class,
            ],
            'Negative float value' => [
                'raw' => '-1337.42',
                'transformed' => '-1337.42',
                'type' => FloatValueType::class,
            ],
            'Negative float value followed by description' => [
                'raw' => '-1337.42 lorem ipsum',
                'transformed' => '-1337.42',
                'type' => FloatValueType::class,
            ],
            'Integer type' => [
                'raw' => 'int',
                'transformed' => 'int',
                'type' => IntegerType::class,
            ],
            'Integer type - uppercase' => [
                'raw' => 'INT',
                'transformed' => 'int',
                'type' => IntegerType::class,
            ],
            'Integer type followed by description' => [
                'raw' => 'int lorem ipsum',
                'transformed' => 'int',
                'type' => IntegerType::class,
            ],
            'Integer type (longer version)' => [
                'raw' => 'integer',
                'transformed' => 'int',
                'type' => IntegerType::class,
            ],
            'Integer type (longer version) - uppercase' => [
                'raw' => 'INTEGER',
                'transformed' => 'int',
                'type' => IntegerType::class,
            ],
            'Positive integer type' => [
                'raw' => 'positive-int',
                'transformed' => 'positive-int',
                'type' => IntegerType::class,
            ],
            'Positive integer type - uppercase' => [
                'raw' => 'POSITIVE-INT',
                'transformed' => 'positive-int',
                'type' => IntegerType::class,
            ],
            'Positive integer type followed by description' => [
                'raw' => 'positive-int lorem ipsum',
                'transformed' => 'positive-int',
                'type' => IntegerType::class,
            ],
            'Negative integer type' => [
                'raw' => 'negative-int',
                'transformed' => 'negative-int',
                'type' => IntegerType::class,
            ],
            'Negative integer type - uppercase' => [
                'raw' => 'NEGATIVE-INT',
                'transformed' => 'negative-int',
                'type' => IntegerType::class,
            ],
            'Negative integer type followed by description' => [
                'raw' => 'negative-int lorem ipsum',
                'transformed' => 'negative-int',
                'type' => IntegerType::class,
            ],
            'Positive integer value' => [
                'raw' => '1337',
                'transformed' => '1337',
                'type' => IntegerValueType::class,
            ],
            'Positive integer value followed by description' => [
                'raw' => '1337 lorem ipsum',
                'transformed' => '1337',
                'type' => IntegerValueType::class,
            ],
            'Negative integer value' => [
                'raw' => '-1337',
                'transformed' => '-1337',
                'type' => IntegerValueType::class,
            ],
            'Negative integer value followed by description' => [
                'raw' => '-1337 lorem ipsum',
                'transformed' => '-1337',
                'type' => IntegerValueType::class,
            ],
            'Integer range' => [
                'raw' => 'int<42, 1337>',
                'transformed' => 'int<42, 1337>',
                'type' => IntegerRangeType::class,
            ],
            'Integer range with negative values' => [
                'raw' => 'int<-1337, -42>',
                'transformed' => 'int<-1337, -42>',
                'type' => IntegerRangeType::class,
            ],
            'Integer range with min and max values' => [
                'raw' => 'int<min, max>',
                'transformed' => 'int<min, max>',
                'type' => IntegerRangeType::class,
            ],
            'Integer range followed by description' => [
                'raw' => 'int<42, 1337> lorem ipsum',
                'transformed' => 'int<42, 1337>',
                'type' => IntegerRangeType::class,
            ],
            'String type' => [
                'raw' => 'string',
                'transformed' => 'string',
                'type' => StringType::class,
            ],
            'String type - uppercase' => [
                'raw' => 'STRING',
                'transformed' => 'string',
                'type' => StringType::class,
            ],
            'String type followed by description' => [
                'raw' => 'string lorem ipsum',
                'transformed' => 'string',
                'type' => StringType::class,
            ],
            'Non empty string type' => [
                'raw' => 'non-empty-string',
                'transformed' => 'non-empty-string',
                'type' => NonEmptyStringType::class,
            ],
            'Non empty string type - uppercase' => [
                'raw' => 'NON-EMPTY-STRING',
                'transformed' => 'non-empty-string',
                'type' => NonEmptyStringType::class,
            ],
            'Non empty string type followed by description' => [
                'raw' => 'non-empty-string lorem ipsum',
                'transformed' => 'non-empty-string',
                'type' => NonEmptyStringType::class,
            ],
            'String value with single quote' => [
                'raw' => "'foo'",
                'transformed' => "'foo'",
                'type' => StringValueType::class,
            ],
            'String value with single quote followed by description' => [
                'raw' => "'foo' lorem ipsum",
                'transformed' => "'foo'",
                'type' => StringValueType::class,
            ],
            'String value with double quote' => [
                'raw' => '"foo"',
                'transformed' => '"foo"',
                'type' => StringValueType::class,
            ],
            'String value with double quote followed by description' => [
                'raw' => '"foo" lorem ipsum',
                'transformed' => '"foo"',
                'type' => StringValueType::class,
            ],
            'Boolean type' => [
                'raw' => 'bool',
                'transformed' => 'bool',
                'type' => NativeBooleanType::class,
            ],
            'Boolean type - uppercase' => [
                'raw' => 'BOOL',
                'transformed' => 'bool',
                'type' => NativeBooleanType::class,
            ],
            'Boolean type (longer version)' => [
                'raw' => 'boolean',
                'transformed' => 'bool',
                'type' => NativeBooleanType::class,
            ],
            'Boolean type (longer version) - uppercase' => [
                'raw' => 'BOOLEAN',
                'transformed' => 'bool',
                'type' => NativeBooleanType::class,
            ],
            'Boolean type followed by description' => [
                'raw' => 'bool lorem ipsum',
                'transformed' => 'bool',
                'type' => NativeBooleanType::class,
            ],
            'Undefined object type' => [
                'raw' => 'object',
                'transformed' => 'object',
                'type' => UndefinedObjectType::class,
            ],
            'Undefined object type - uppercase' => [
                'raw' => 'OBJECT',
                'transformed' => 'object',
                'type' => UndefinedObjectType::class,
            ],
            'Undefined object type followed by description' => [
                'raw' => 'object lorem ipsum',
                'transformed' => 'object',
                'type' => UndefinedObjectType::class,
            ],
            'Array native type' => [
                'raw' => 'array',
                'transformed' => 'array',
                'type' => ArrayType::class,
            ],
            'Array native type - uppercase' => [
                'raw' => 'ARRAY',
                'transformed' => 'array',
                'type' => ArrayType::class,
            ],
            'Array native type followed by description' => [
                'raw' => 'array lorem ipsum',
                'transformed' => 'array',
                'type' => ArrayType::class,
            ],
            'Simple array type' => [
                'raw' => 'float[]',
                'transformed' => 'float[]',
                'type' => ArrayType::class,
            ],
            'Simple array type followed by description' => [
                'raw' => 'float[] lorem ipsum',
                'transformed' => 'float[]',
                'type' => ArrayType::class,
            ],
            'Array type with string array-key' => [
                'raw' => 'array<string, float>',
                'transformed' => 'array<string, float>',
                'type' => ArrayType::class,
            ],
            'Array type with int array-key' => [
                'raw' => 'array<int, float>',
                'transformed' => 'array<int, float>',
                'type' => ArrayType::class,
            ],
            'Array type with array-key' => [
                'raw' => 'array<array-key, float>',
                'transformed' => 'array<float>',
                'type' => ArrayType::class,
            ],
            'Array without array-key' => [
                'raw' => 'array<float>',
                'transformed' => 'array<float>',
                'type' => ArrayType::class,
            ],
            'Array without array-key followed by description' => [
                'raw' => 'array<float> lorem ipsum',
                'transformed' => 'array<float>',
                'type' => ArrayType::class,
            ],
            'Non empty native array' => [
                'raw' => 'non-empty-array',
                'transformed' => 'non-empty-array',
                'type' => NonEmptyArrayType::class,
            ],
            'Non empty native array - uppercase' => [
                'raw' => 'NON-EMPTY-ARRAY',
                'transformed' => 'non-empty-array',
                'type' => NonEmptyArrayType::class,
            ],
            'Non empty native array followed by description' => [
                'raw' => 'non-empty-array lorem ipsum',
                'transformed' => 'non-empty-array',
                'type' => NonEmptyArrayType::class,
            ],
            'Non empty array type with string array-key' => [
                'raw' => 'non-empty-array<string, float>',
                'transformed' => 'non-empty-array<string, float>',
                'type' => NonEmptyArrayType::class,
            ],
            'Non empty array type with int array-key' => [
                'raw' => 'non-empty-array<int, float>',
                'transformed' => 'non-empty-array<int, float>',
                'type' => NonEmptyArrayType::class,
            ],
            'Non empty array type with array-key' => [
                'raw' => 'non-empty-array<array-key, float>',
                'transformed' => 'non-empty-array<float>',
                'type' => NonEmptyArrayType::class,
            ],
            'Non empty array without array-key' => [
                'raw' => 'non-empty-array<float>',
                'transformed' => 'non-empty-array<float>',
                'type' => NonEmptyArrayType::class,
            ],
            'Non empty array without array-key followed by description' => [
                'raw' => 'non-empty-array<float> lorem ipsum',
                'transformed' => 'non-empty-array<float>',
                'type' => NonEmptyArrayType::class,
            ],
            'List native type' => [
                'raw' => 'list',
                'transformed' => 'list',
                'type' => ListType::class,
            ],
            'List native type - uppercase' => [
                'raw' => 'LIST',
                'transformed' => 'list',
                'type' => ListType::class,
            ],
            'List native type followed by description' => [
                'raw' => 'list lorem ipsum',
                'transformed' => 'list',
                'type' => ListType::class,
            ],
            'List type' => [
                'raw' => 'list<float>',
                'transformed' => 'list<float>',
                'type' => ListType::class,
            ],
            'List type followed by description' => [
                'raw' => 'list<float> lorem ipsum',
                'transformed' => 'list<float>',
                'type' => ListType::class,
            ],
            'Non empty list native type' => [
                'raw' => 'non-empty-list',
                'transformed' => 'non-empty-list',
                'type' => NonEmptyListType::class,
            ],
            'Non empty list native type - uppercase' => [
                'raw' => 'NON-EMPTY-LIST',
                'transformed' => 'non-empty-list',
                'type' => NonEmptyListType::class,
            ],
            'Non empty list native type followed by description' => [
                'raw' => 'non-empty-list lorem ipsum',
                'transformed' => 'non-empty-list',
                'type' => NonEmptyListType::class,
            ],
            'Non empty list' => [
                'raw' => 'non-empty-list<float>',
                'transformed' => 'non-empty-list<float>',
                'type' => NonEmptyListType::class,
            ],
            'Non empty list followed by description' => [
                'raw' => 'non-empty-list<float> lorem ipsum',
                'transformed' => 'non-empty-list<float>',
                'type' => NonEmptyListType::class,
            ],
            'Shaped array' => [
                'raw' => 'array{foo: string}',
                'transformed' => 'array{foo: string}',
                'type' => ShapedArrayType::class,
            ],
            'Shaped array with single quote key' => [
                'raw' => "array{'foo': string}",
                'transformed' => "array{'foo': string}",
                'type' => ShapedArrayType::class,
            ],
            'Shaped array with double quote key' => [
                'raw' => 'array{"foo": string}',
                'transformed' => 'array{"foo": string}',
                'type' => ShapedArrayType::class,
            ],
            'Shaped array with several keys' => [
                'raw' => 'array{foo: string, bar: int}',
                'transformed' => 'array{foo: string, bar: int}',
                'type' => ShapedArrayType::class,
            ],
            'Shaped array with several quote keys' => [
                'raw' => 'array{\'foo\': string, "bar": int}',
                'transformed' => 'array{\'foo\': string, "bar": int}',
                'type' => ShapedArrayType::class,
            ],
            'Shaped array with no key' => [
                'raw' => 'array{string, int}',
                'transformed' => 'array{0: string, 1: int}',
                'type' => ShapedArrayType::class,
            ],
            'Shaped array with optional key' => [
                'raw' => 'array{foo: string, bar?: int}',
                'transformed' => 'array{foo: string, bar?: int}',
                'type' => ShapedArrayType::class,
            ],
            'Shaped array with reserved keyword as key' => [
                'raw' => 'array{string: string}',
                'transformed' => 'array{string: string}',
                'type' => ShapedArrayType::class,
            ],
            'Shaped array followed by description' => [
                'raw' => 'array{foo: string} lorem ipsum',
                'transformed' => 'array{foo: string}',
                'type' => ShapedArrayType::class,
            ],
            'Iterable type' => [
                'raw' => 'iterable',
                'transformed' => 'iterable',
                'type' => IterableType::class,
            ],
            'Iterable type - uppercase' => [
                'raw' => 'ITERABLE',
                'transformed' => 'iterable',
                'type' => IterableType::class,
            ],
            'Iterable type with string array-key' => [
                'raw' => 'iterable<string, float>',
                'transformed' => 'iterable<string, float>',
                'type' => IterableType::class,
            ],
            'Iterable type with int array-key' => [
                'raw' => 'iterable<int, float>',
                'transformed' => 'iterable<int, float>',
                'type' => IterableType::class,
            ],
            'Iterable type with array-key' => [
                'raw' => 'iterable<array-key, float>',
                'transformed' => 'iterable<float>',
                'type' => IterableType::class,
            ],
            'Iterable without array-key' => [
                'raw' => 'iterable<float>',
                'transformed' => 'iterable<float>',
                'type' => IterableType::class,
            ],
            'Iterable without array-key followed by description' => [
                'raw' => 'iterable<float> lorem ipsum',
                'transformed' => 'iterable<float>',
                'type' => IterableType::class,
            ],
            'Class string' => [
                'raw' => 'class-string',
                'transformed' => 'class-string',
                'type' => ClassStringType::class,
            ],
            'Class string followed by description' => [
                'raw' => 'class-string lorem ipsum',
                'transformed' => 'class-string',
                'type' => ClassStringType::class,
            ],
            'Class string of class' => [
                'raw' => 'class-string<stdClass>',
                'transformed' => 'class-string<stdClass>',
                'type' => ClassStringType::class,
            ],
            'Class string of class followed by description' => [
                'raw' => 'class-string<stdClass> lorem ipsum',
                'transformed' => 'class-string<stdClass>',
                'type' => ClassStringType::class,
            ],
            'Class string of interface' => [
                'raw' => 'class-string<DateTimeInterface>',
                'transformed' => 'class-string<DateTimeInterface>',
                'type' => ClassStringType::class,
            ],
            'Class string of union' => [
                'raw' => 'class-string<DateTimeInterface|stdClass>',
                'transformed' => 'class-string<DateTimeInterface|stdClass>',
                'type' => ClassStringType::class,
            ],
            'Class name' => [
                'raw' => stdClass::class,
                'transformed' => stdClass::class,
                'type' => ClassType::class,
            ],
            'Class name followed by description' => [
                'raw' => 'stdClass lorem ipsum',
                'transformed' => stdClass::class,
                'type' => ClassType::class,
            ],
            'Abstract class name' => [
                'raw' => AbstractObject::class,
                'transformed' => AbstractObject::class,
                'type' => InterfaceType::class,
            ],
            'Interface name' => [
                'raw' => DateTimeInterface::class,
                'transformed' => DateTimeInterface::class,
                'type' => InterfaceType::class,
            ],
            'Nullable type' => [
                'raw' => '?string',
                'transformed' => 'null|string',
                'type' => UnionType::class,
            ],
            'Nullable type followed by description' => [
                'raw' => '?string lorem ipsum',
                'transformed' => 'null|string',
                'type' => UnionType::class,
            ],
            'Union type' => [
                'raw' => 'int|float',
                'transformed' => 'int|float',
                'type' => UnionType::class,
            ],
            'Union type with native array' => [
                'raw' => 'array|int',
                'transformed' => 'array|int',
                'type' => UnionType::class,
            ],
            'Union type with simple iterable' => [
                'raw' => 'iterable|int',
                'transformed' => 'iterable|int',
                'type' => UnionType::class,
            ],
            'Union type with simple array' => [
                'raw' => 'int[]|float',
                'transformed' => 'int[]|float',
                'type' => UnionType::class,
            ],
            'Union type with array' => [
                'raw' => 'array<int>|float',
                'transformed' => 'array<int>|float',
                'type' => UnionType::class,
            ],
            'Union type with class-string' => [
                'raw' => 'class-string|int',
                'transformed' => 'class-string|int',
                'type' => UnionType::class,
            ],
            'Union type followed by description' => [
                'raw' => 'int|float lorem ipsum',
                'transformed' => 'int|float',
                'type' => UnionType::class,
            ],
            'Intersection type' => [
                'raw' => 'stdClass&DateTimeInterface',
                'transformed' => 'stdClass&DateTimeInterface',
                'type' => IntersectionType::class,
            ],
            'Intersection type followed by description' => [
                'raw' => 'stdClass&DateTimeInterface lorem ipsum',
                'transformed' => 'stdClass&DateTimeInterface',
                'type' => IntersectionType::class,
            ],
        ];
    }

    public function test_multiple_union_types_are_parsed(): void
    {
        $raw = 'int|float|string';

        $unionType = $this->parser->parse($raw);

        self::assertInstanceOf(UnionType::class, $unionType);

        $types = $unionType->types();

        self::assertInstanceOf(IntegerType::class, $types[0]);
        self::assertInstanceOf(NativeFloatType::class, $types[1]);
        self::assertInstanceOf(StringType::class, $types[2]);
    }

    public function test_missing_right_union_type_throws_exception(): void
    {
        $this->expectException(RightUnionTypeMissing::class);
        $this->expectExceptionCode(1631294715);
        $this->expectExceptionMessage('Right type is missing for union `string|?`.');

        $this->parser->parse('string|');
    }

    public function test_multiple_intersection_types_are_parsed(): void
    {
        $raw = 'stdClass&DateTimeInterface&DateTime';

        $intersectionType = $this->parser->parse($raw);

        self::assertInstanceOf(IntersectionType::class, $intersectionType);

        $types = $intersectionType->types();

        self::assertInstanceOf(ClassType::class, $types[0]);
        self::assertSame(stdClass::class, $types[0]->className());

        self::assertInstanceOf(InterfaceType::class, $types[1]);
        self::assertSame(DateTimeInterface::class, $types[1]->className());

        self::assertInstanceOf(ClassType::class, $types[2]);
        self::assertSame(DateTime::class, $types[2]->className());
    }

    public function test_missing_right_intersection_type_throws_exception(): void
    {
        $this->expectException(RightIntersectionTypeMissing::class);
        $this->expectExceptionCode(1631612575);
        $this->expectExceptionMessage('Right type is missing for intersection `DateTimeInterface&?`.');

        $this->parser->parse('DateTimeInterface&');
    }

    public function test_missing_simple_array_closing_bracket_throws_exception(): void
    {
        $this->expectException(SimpleArrayClosingBracketMissing::class);
        $this->expectExceptionCode(1606474266);
        $this->expectExceptionMessage('The closing bracket is missing for the array expression `string[]`.');

        $this->parser->parse('string[');
    }

    public function test_invalid_array_key_throws_exception(): void
    {
        $this->expectException(InvalidArrayKey::class);
        $this->expectExceptionCode(1604335007);
        $this->expectExceptionMessage('Invalid key type `float` for `array<float, string>`. It must be one of `array-key`, `int` or `string`.');

        $this->parser->parse('array<float, string>');
    }

    public function test_invalid_non_empty_array_key_throws_exception(): void
    {
        $this->expectException(InvalidArrayKey::class);
        $this->expectExceptionCode(1604335007);
        $this->expectExceptionMessage('Invalid key type `float` for `non-empty-array<float, string>`. It must be one of `array-key`, `int` or `string`.');

        $this->parser->parse('non-empty-array<float, string>');
    }

    public function test_missing_array_comma_throws_exception(): void
    {
        $this->expectException(ArrayCommaMissing::class);
        $this->expectExceptionCode(1606483614);
        $this->expectExceptionMessage('A comma is missing for `array<int, ?>`.');

        $this->parser->parse('array<int string>');
    }

    public function test_missing_non_empty_array_comma_throws_exception(): void
    {
        $this->expectException(ArrayCommaMissing::class);
        $this->expectExceptionCode(1606483614);
        $this->expectExceptionMessage('A comma is missing for `non-empty-array<int, ?>`.');

        $this->parser->parse('non-empty-array<int string>');
    }

    public function test_missing_array_closing_bracket_throws_exception(): void
    {
        $this->expectException(ArrayClosingBracketMissing::class);
        $this->expectExceptionCode(1606483975);
        $this->expectExceptionMessage('The closing bracket is missing for `array<int, string>`.');

        $this->parser->parse('array<int, string');
    }

    public function test_missing_non_empty_array_closing_bracket_throws_exception(): void
    {
        $this->expectException(ArrayClosingBracketMissing::class);
        $this->expectExceptionCode(1606483975);
        $this->expectExceptionMessage('The closing bracket is missing for `non-empty-array<int, string>`.');

        $this->parser->parse('non-empty-array<int, string');
    }

    public function test_missing_list_closing_bracket_throws_exception(): void
    {
        $this->expectException(ListClosingBracketMissing::class);
        $this->expectExceptionCode(1634035071);
        $this->expectExceptionMessage('The closing bracket is missing for `list<string>`.');

        $this->parser->parse('list<string');
    }

    public function test_missing_non_empty_list_closing_bracket_throws_exception(): void
    {
        $this->expectException(ListClosingBracketMissing::class);
        $this->expectExceptionCode(1634035071);
        $this->expectExceptionMessage('The closing bracket is missing for `non-empty-list<string>`.');

        $this->parser->parse('non-empty-list<string');
    }

    public function test_invalid_iterable_key_throws_exception(): void
    {
        $this->expectException(InvalidIterableKey::class);
        $this->expectExceptionCode(1618994708);
        $this->expectExceptionMessage('Invalid key type `float` for `iterable<float, string>`. It must be one of `array-key`, `int` or `string`.');

        $this->parser->parse('iterable<float, string>');
    }

    public function test_missing_iterable_comma_throws_exception(): void
    {
        $this->expectException(IterableCommaMissing::class);
        $this->expectExceptionCode(1618994669);
        $this->expectExceptionMessage('A comma is missing for `iterable<int, ?>`.');

        $this->parser->parse('iterable<int string>');
    }

    public function test_missing_iterable_closing_bracket_throws_exception(): void
    {
        $this->expectException(IterableClosingBracketMissing::class);
        $this->expectExceptionCode(1618994728);
        $this->expectExceptionMessage('The closing bracket is missing for `iterable<int, string>`.');

        $this->parser->parse('iterable<int, string');
    }

    public function test_missing_class_string_closing_bracket_throws_exception(): void
    {
        $this->expectException(ClassStringClosingBracketMissing::class);
        $this->expectExceptionCode(1606484169);
        $this->expectExceptionMessage('The closing bracket is missing for the class string expression `class-string<DateTimeInterface>`.');

        $this->parser->parse('class-string<DateTimeInterface');
    }

    public function test_invalid_class_string_type_throws_exception(): void
    {
        $this->expectException(InvalidClassStringSubType::class);
        $this->expectExceptionCode(1608034138);
        $this->expectExceptionMessage('Invalid class string type `int`, it must be a class name or an interface name.');

        $this->parser->parse('class-string<int');
    }

    public function test_invalid_left_intersection_member_throws_exception(): void
    {
        $this->expectException(InvalidIntersectionType::class);
        $this->expectExceptionCode(1608030163);
        $this->expectExceptionMessage('Invalid intersection member `int`, it must be a class name or an interface name.');

        $this->parser->parse('int&DateTimeInterface');
    }

    public function test_invalid_right_intersection_member_throws_exception(): void
    {
        $this->expectException(InvalidIntersectionType::class);
        $this->expectExceptionCode(1608030163);
        $this->expectExceptionMessage('Invalid intersection member `int`, it must be a class name or an interface name.');

        $this->parser->parse('DateTimeInterface&int');
    }

    public function test_shaped_array_empty_elements_throws_exception(): void
    {
        $this->expectException(ShapedArrayEmptyElements::class);
        $this->expectExceptionCode(1631286932);
        $this->expectExceptionMessage('Shaped array must define one or more elements, for instance `array{foo: string}`.');

        $this->parser->parse('array{}');
    }

    public function test_shaped_array_closing_bracket_missing_throws_exception(): void
    {
        $this->expectException(ShapedArrayClosingBracketMissing::class);
        $this->expectExceptionCode(1631283658);
        $this->expectExceptionMessage('Missing closing curly bracket in shaped array signature `array{0: string`.');

        $this->parser->parse('array{string');
    }

    public function test_shaped_array_closing_bracket_missing_after_other_element_throws_exception(): void
    {
        $this->expectException(ShapedArrayClosingBracketMissing::class);
        $this->expectExceptionCode(1631283658);
        $this->expectExceptionMessage('Missing closing curly bracket in shaped array signature `array{0: int, foo: string`.');

        $this->parser->parse('array{int, foo: string');
    }

    public function test_shaped_array_colon_missing_throws_exception(): void
    {
        $this->expectException(ShapedArrayColonTokenMissing::class);
        $this->expectExceptionCode(1631283847);
        $this->expectExceptionMessage('A colon symbol is missing in shaped array signature `array{string?`.');

        $this->parser->parse('array{string?');
    }

    public function test_shaped_array_colon_missing_after_other_element_throws_exception(): void
    {
        $this->expectException(ShapedArrayColonTokenMissing::class);
        $this->expectExceptionCode(1631283847);
        $this->expectExceptionMessage('A colon symbol is missing in shaped array signature `array{0: int, foo?`.');

        $this->parser->parse('array{int, foo?');
    }

    public function test_shaped_array_closing_bracket_missing_after_unfinished_element_throws_exception(): void
    {
        $this->expectException(ShapedArrayElementTypeMissing::class);
        $this->expectExceptionCode(1631286250);
        $this->expectExceptionMessage('Missing element type in shaped array signature `array{0: int, foo?:`.');

        $this->parser->parse('array{int, foo?:');
    }

    public function test_shaped_array_colon_expected_but_other_symbol_throws_exception(): void
    {
        $this->expectException(ShapedArrayColonTokenMissing::class);
        $this->expectExceptionCode(1631283847);
        $this->expectExceptionMessage('A colon symbol is missing in shaped array signature `array{0: int, foo?`.');

        $this->parser->parse('array{int, foo?;');
    }

    public function test_shaped_array_comma_expected_but_other_symbol_throws_exception(): void
    {
        $this->expectException(ShapedArrayCommaMissing::class);
        $this->expectExceptionCode(1631286589);
        $this->expectExceptionMessage('Comma missing in shaped array signature `array{0: int, 1: string`.');

        $this->parser->parse('array{int, string]');
    }

    public function test_missing_min_value_for_integer_range_throws_exception(): void
    {
        $this->expectException(IntegerRangeMissingMinValue::class);
        $this->expectExceptionCode(1638787061);
        $this->expectExceptionMessage('Missing min value for integer range, its signature must match `int<min, max>`.');

        $this->parser->parse('int<');
    }

    public function test_invalid_min_value_for_integer_range_throws_exception(): void
    {
        $this->expectException(IntegerRangeInvalidMinValue::class);
        $this->expectExceptionCode(1638787807);
        $this->expectExceptionMessage('Invalid type `string` for min value of integer range, it must be either `min` or an integer value.');

        $this->parser->parse('int<string, 1337>');
    }

    public function test_missing_comma_for_integer_range_throws_exception(): void
    {
        $this->expectException(IntegerRangeMissingComma::class);
        $this->expectExceptionCode(1638787915);
        $this->expectExceptionMessage('Missing comma in integer range signature `int<42, ?>`.');

        $this->parser->parse('int<42 1337>');
    }

    public function test_missing_max_value_for_integer_range_throws_exception(): void
    {
        $this->expectException(IntegerRangeMissingMaxValue::class);
        $this->expectExceptionCode(1638788092);
        $this->expectExceptionMessage('Missing max value for integer range, its signature must match `int<42, max>`.');

        $this->parser->parse('int<42,');
    }

    public function test_invalid_max_value_for_integer_range_throws_exception(): void
    {
        $this->expectException(IntegerRangeInvalidMaxValue::class);
        $this->expectExceptionCode(1638788172);
        $this->expectExceptionMessage('Invalid type `string` for max value of integer range `int<42, ?>`, it must be either `max` or an integer value.');

        $this->parser->parse('int<42, string>');
    }

    public function test_missing_closing_bracket_for_integer_range_throws_exception(): void
    {
        $this->expectException(IntegerRangeMissingClosingBracket::class);
        $this->expectExceptionCode(1638788306);
        $this->expectExceptionMessage('Missing closing bracket in integer range signature `int<42, 1337>`.');

        $this->parser->parse('int<42, 1337');
    }

    public function test_missing_closing_single_quote_throws_exception(): void
    {
        $this->expectException(UnknownSymbol::class);
        $this->expectExceptionCode(1632918723);
        $this->expectExceptionMessage("Cannot parse unknown symbol `'foo`.");

        $this->parser->parse("'foo");
    }

    public function test_missing_closing_double_quote_throws_exception(): void
    {
        $this->expectException(UnknownSymbol::class);
        $this->expectExceptionCode(1632918723);
        $this->expectExceptionMessage('Cannot parse unknown symbol `"foo`.');

        $this->parser->parse('"foo');
    }
}
