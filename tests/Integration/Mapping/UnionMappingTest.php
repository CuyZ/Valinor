<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class UnionMappingTest extends IntegrationTestCase
{
    #[DataProvider('union_mapping_works_properly_data_provider')]
    public function test_union_mapping_works_properly(string $type, mixed $source, callable $assertion): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(SomeInterfaceForObjectWithOneStringValue::class, fn () => SomeObjectWithOneStringValue::class)
                ->mapper()
                ->map($type, $source);

            $assertion($result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    #[DataProvider('union_mapping_works_properly_data_provider')]
    public function test_union_mapping_works_properly_with_superfluous_keys_allowed(string $type, mixed $source, callable $assertion): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(SomeInterfaceForObjectWithOneStringValue::class, fn () => SomeObjectWithOneStringValue::class)
                ->allowSuperfluousKeys()
                ->mapper()
                ->map($type, $source);

            $assertion($result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    #[DataProvider('union_mapping_works_properly_with_flexible_casting_enabled_data_provider')]
    public function test_union_mapping_works_properly_with_flexible_casting_enabled(string $type, mixed $source, callable $assertion): void
    {
        try {
            $result = $this->mapperBuilder()
                ->enableFlexibleCasting()
                ->mapper()
                ->map($type, $source);

            $assertion($result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public static function union_mapping_works_properly_data_provider(): iterable
    {
        yield 'string or list of string, with string' => [
            'type' => 'string|list<string>',
            'source' => 'foo',
            'assertion' => fn (mixed $result) => self::assertSame('foo', $result),
        ];

        yield 'string or list of string, with list of string' => [
            'type' => 'string|list<string>',
            'source' => ['foo', 'bar'],
            'assertion' => fn (mixed $result) => self::assertSame(['foo', 'bar'], $result),
        ];

        yield 'list of string or list of integer, with list of string' => [
            'type' => 'list<string>|list<int>',
            'source' => ['foo', 'bar'],
            'assertion' => fn (mixed $result) => self::assertSame(['foo', 'bar'], $result),
        ];

        yield 'list of string or list of integer, with list of integer' => [
            'type' => 'list<string>|list<int>',
            'source' => [42, 1337],
            'assertion' => fn (mixed $result) => self::assertSame([42, 1337], $result),
        ];

        yield 'shaped array with integer or array of integer, with integer' => [
            'type' => 'array{key: int|array<int>}',
            'source' => ['key' => 42],
            'assertion' => fn (mixed $result) => self::assertSame(['key' => 42], $result),
        ];

        yield 'shaped array with integer or array of integer, with array of integer' => [
            'type' => 'array{key: int|array<int>}',
            'source' => ['key' => [42, 1337]],
            'assertion' => fn (mixed $result) => self::assertSame(['key' => [42, 1337]], $result),
        ];

        yield 'shaped array representing http response with status 200' => [
            'type' => 'array{status: 200, data: array{text: string}} | array{status: 400, error: string}',
            'source' => ['status' => 200, 'data' => ['text' => 'foo']],
            'assertion' => fn (mixed $result) => self::assertSame(['status' => 200, 'data' => ['text' => 'foo']], $result),
        ];

        yield 'shaped array representing http response with status 400' => [
            'type' => 'array{status: 200, data: array{text: string}} | array{status: 400, error: string}',
            'source' => ['status' => 400, 'error' => 'foo'],
            'assertion' => fn (mixed $result) => self::assertSame(['status' => 400, 'error' => 'foo'], $result),
        ];

        yield 'array of string or object with one string value, with array of string' => [
            'type' => 'array<string>|' . SomeObjectWithOneStringValue::class,
            'source' => ['foo', 'bar'],
            'assertion' => fn (mixed $result) => self::assertSame(['foo', 'bar'], $result),
        ];

        yield 'array of string or object with one string value, with scalar' => [
            'type' => 'array<string>|' . SomeObjectWithOneStringValue::class,
            'source' => 'foo',
            'assertion' => function (mixed $result) {
                self::assertInstanceOf(SomeObjectWithOneStringValue::class, $result);
                self::assertSame('foo', $result->string);
            },
        ];

        yield 'array of string or object with one string value, with array containing value for object' => [
            'type' => 'array<string>|' . SomeObjectWithOneStringValue::class,
            'source' => ['string' => 'foo'],
            'assertion' => function (mixed $result) {
                self::assertInstanceOf(SomeObjectWithOneStringValue::class, $result);
                self::assertSame('foo', $result->string);
            },
        ];

        yield 'string or object with one string value, with string value' => [
            'type' => 'string|' . SomeObjectWithOneStringValue::class,
            'source' => 'foo',
            'assertion' => fn (mixed $result) => self::assertSame('foo', $result),
        ];

        yield 'string value or object with one string value, with matching string value' => [
            'type' => "'foo'|" . SomeObjectWithOneStringValue::class,
            'source' => 'foo',
            'assertion' => fn (mixed $result) => self::assertSame('foo', $result),
        ];

        yield 'string value or object with one string value, with non-matching string value' => [
            'type' => "'foo'|" . SomeObjectWithOneStringValue::class,
            'source' => 'bar',
            'assertion' => function (mixed $result) {
                self::assertInstanceOf(SomeObjectWithOneStringValue::class, $result);
                self::assertSame('bar', $result->string);
            },
        ];

        yield 'object with one string value or object with one string value and one integer value, with array containing string' => [
            'type' => SomeObjectWithOneStringValue::class . '|' . SomeObjectWithOneStringValueAndOneIntValue::class,
            'source' => ['string' => 'foo'],
            'assertion' => function (mixed $result) {
                self::assertInstanceOf(SomeObjectWithOneStringValue::class, $result);
                self::assertSame('foo', $result->string);
            },
        ];

        yield 'object with one string value or object with one string value and one integer value, with array containing string and integer' => [
            'type' => SomeObjectWithOneStringValue::class . '|' . SomeObjectWithOneStringValueAndOneIntValue::class,
            'source' => [
                'string' => 'foo',
                'integer' => 42,
            ],
            'assertion' => function (mixed $result) {
                self::assertInstanceOf(SomeObjectWithOneStringValueAndOneIntValue::class, $result);
                self::assertSame('foo', $result->string);
                self::assertSame(42, $result->integer);
            },
        ];

        yield 'array of string or interface for object with one string value or object with one string value and one integer value, with array containing string' => [
            'type' => 'array<string>|' . SomeInterfaceForObjectWithOneStringValue::class . '|' . SomeObjectWithOneStringValueAndOneIntValue::class,
            'source' => ['string' => 'foo'],
            'assertion' => function (mixed $result) {
                self::assertInstanceOf(SomeObjectWithOneStringValue::class, $result);
                self::assertSame('foo', $result->string);
            },
        ];

        yield 'array of string or interface for object with one string value or object with one string value and one integer value, with array containing string and integer' => [
            'type' => 'array<string>|' . SomeInterfaceForObjectWithOneStringValue::class . '|' . SomeObjectWithOneStringValueAndOneIntValue::class,
            'source' => [
                'string' => 'foo',
                'integer' => 42,
            ],
            'assertion' => function (mixed $result) {
                self::assertInstanceOf(SomeObjectWithOneStringValueAndOneIntValue::class, $result);
                self::assertSame('foo', $result->string);
                self::assertSame(42, $result->integer);
            },
        ];
    }

    public static function union_mapping_works_properly_with_flexible_casting_enabled_data_provider(): iterable
    {
        yield 'float or integer, with string containing float' => [
            'type' => 'float|integer',
            'source' => '42.0',
            'assertion' => fn (mixed $result) => self::assertSame(42.0, $result),
        ];

        yield 'float or integer, with string containing integer' => [
            'type' => 'float|integer',
            'source' => '42',
            'assertion' => fn (mixed $result) => self::assertSame(42, $result),
        ];

        yield 'boolean or integer, with string containing integer' => [
            'type' => 'bool|int',
            'source' => '1',
            'assertion' => fn (mixed $result) => self::assertSame(1, $result),
        ];

        yield 'integer or boolean, with string containing integer' => [
            'type' => 'int|bool',
            'source' => '1',
            'assertion' => fn (mixed $result) => self::assertSame(1, $result),
        ];

        yield 'boolean or float, with string containing integer' => [
            'type' => 'bool|float',
            'source' => '1',
            'assertion' => fn (mixed $result) => self::assertSame(1.0, $result),
        ];

        yield 'float or boolean, with string containing integer' => [
            'type' => 'float|bool',
            'source' => '1',
            'assertion' => fn (mixed $result) => self::assertSame(1.0, $result),
        ];

        yield 'boolean or string, with integer' => [
            'type' => 'bool|string',
            'source' => 1,
            'assertion' => fn (mixed $result) => self::assertSame('1', $result),
        ];

        yield 'string or boolean, with integer' => [
            'type' => 'string|bool',
            'source' => 1,
            'assertion' => fn (mixed $result) => self::assertSame('1', $result),
        ];

        yield 'array of integer or boolean, with integer' => [
            'type' => 'array<int>|bool|true',
            'source' => 1,
            'assertion' => fn (mixed $result) => self::assertSame(true, $result),
        ];
    }

    public function test_shaped_array_representing_http_response_with_status_200_with_superfluous_key(): void
    {
        $result = $this->mapperBuilder()
            ->allowSuperfluousKeys()
            ->mapper()
            ->map('array{status: 200, data: array{text: string}} | array{status: 400, error: string}', [
                'status' => 200,
                'data' => [
                    'text' => 'foo',
                    'superfluous' => 'key',
                ],
            ]);

        self::assertSame(['status' => 200, 'data' => ['text' => 'foo']], $result);
    }

    public function test_scalar_value_matching_two_objects_in_union_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->allowSuperfluousKeys()
                ->mapper()
                ->map(SomeObjectWithOneStringValue::class . '|' . AnotherObjectWithOneStringValue::class, 'foo');

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1710262975', $error->code());
            self::assertSame("Invalid value 'foo', it matches two or more types from union: cannot take a decision.", (string)$error);
        }
    }

    public function test_array_value_matching_two_objects_in_union_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->allowSuperfluousKeys()
                ->mapper()
                ->map(
                    SomeObjectWithOneStringValue::class . '|' . AnotherObjectWithOneStringValue::class,
                    ['string' => 'foo'],
                );

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1710262975', $error->code());
            self::assertSame("Invalid value array{string: 'foo'}, it matches two or more types from union: cannot take a decision.", (string)$error);
        }
    }

    public function test_array_value_matching_two_arrays_in_union_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map(
                    "array<string>|array<'foo'|'bar'>",
                    ['foo', 'bar'],
                );

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1710262975', $error->code());
            self::assertSame("Invalid value array{0: 'foo', 1: 'bar'}, it matches two or more types from `array<string>`, `array<'foo'|'bar'>`: cannot take a decision.", (string)$error);
        }
    }

    public function test_array_value_matching_array_shape_and_object_in_union_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map(
                    'array{string: string}|' . SomeObjectWithOneStringValue::class,
                    ['string' => 'foo'],
                );

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1710262975', $error->code());
            self::assertSame("Invalid value array{string: 'foo'}, it matches two or more types from union: cannot take a decision.", (string)$error);
        }
    }
}

interface SomeInterfaceForObjectWithOneStringValue {}

final class SomeObjectWithOneStringValue implements SomeInterfaceForObjectWithOneStringValue
{
    public function __construct(public readonly string $string) {}
}

final class AnotherObjectWithOneStringValue
{
    public function __construct(public readonly string $string) {}
}

final class SomeObjectWithOneStringValueAndOneIntValue
{
    public function __construct(
        public readonly string $string,
        public readonly int $integer,
    ) {}
}
