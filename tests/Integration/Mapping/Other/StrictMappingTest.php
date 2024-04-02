<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Exception\PermissiveTypeNotAllowed;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Utility\PermissiveTypeFound;
use stdClass;

final class StrictMappingTest extends IntegrationTestCase
{
    public function test_missing_value_throws_exception(): void
    {
        $class = new class () {
            public string $foo;

            public string $bar;
        };

        try {
            $this->mapperBuilder()->mapper()->map($class::class, [
                'foo' => 'foo',
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['bar']->messages()[0];

            self::assertSame('1655449641', $error->code());
            self::assertSame('Cannot be empty and must be filled with a value matching type `string`.', (string)$error);
        }
    }

    public function test_map_to_undefined_object_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeFound::class);
        $this->expectExceptionCode(1655231817);
        $this->expectExceptionMessage('Type `object` is too permissive.');

        $this->mapperBuilder()->mapper()->map('object', new stdClass());
    }

    public function test_map_to_object_containing_undefined_object_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeNotAllowed::class);
        $this->expectExceptionCode(1655389255);
        $this->expectExceptionMessage('Error for `value` in `' . ObjectContainingUndefinedObjectType::class . ' (properties)`: Type `object` is too permissive.');

        $this->mapperBuilder()->mapper()->map(ObjectContainingUndefinedObjectType::class, ['value' => new stdClass()]);
    }

    public function test_map_to_shaped_array_containing_mixed_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeFound::class);
        $this->expectExceptionCode(1655231817);
        $this->expectExceptionMessage('Type `mixed` in `array{foo: string, bar: mixed}` is too permissive.');

        $this->mapperBuilder()->mapper()->map('array{foo: string, bar: mixed}', ['foo' => 'foo', 'bar' => 42]);
    }

    public function test_map_to_unsealed_shaped_array_without_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeFound::class);
        $this->expectExceptionCode(1655231817);
        $this->expectExceptionMessage('Type `mixed` in `array{foo: string, ...}` is too permissive.');

        $this->mapperBuilder()->mapper()->map('array{foo: string, ...}', ['foo' => 'foo', 'bar' => 42]);
    }

    public function test_map_to_object_containing_mixed_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeNotAllowed::class);
        $this->expectExceptionCode(1655389255);
        $this->expectExceptionMessage('Error for `value` in `' . ObjectContainingMixedType::class . ' (properties)`: Type `mixed` in `array{foo: string, bar: mixed}` is too permissive.');

        $this->mapperBuilder()->mapper()->map(ObjectContainingMixedType::class, ['value' => 'foo']);
    }

    public function test_null_that_cannot_be_cast_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('int', null);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('Value null is not a valid integer.', (string)$error);
        }
    }

    public function test_invalid_float_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('float', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value 'foo' is not a valid float.", (string)$error);
        }
    }

    public function test_invalid_float_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('42.404', 1337);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('Value 1337 does not match float value 42.404.', (string)$error);
        }
    }

    public function test_invalid_integer_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('int', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value 'foo' is not a valid integer.", (string)$error);
        }
    }

    public function test_invalid_integer_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('42', 1337);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('Value 1337 does not match integer value 42.', (string)$error);
        }
    }

    public function test_invalid_integer_range_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('int<42, 1337>', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value 'foo' is not a valid integer between 42 and 1337.", (string)$error);
        }
    }

    public function test_invalid_enum_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(PureEnum::class, 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value 'foo' does not match any of 'FOO', 'BAR', 'BAZ'.", (string)$error);
        }
    }

    public function test_invalid_string_enum_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(BackedStringEnum::class, new stdClass());
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value object(stdClass) does not match any of 'foo', 'bar', 'baz'.", (string)$error);
        }
    }

    public function test_invalid_integer_enum_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(BackedIntegerEnum::class, false);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value false does not match any of 42, 404, 1337.", (string)$error);
        }
    }

    public function test_invalid_union_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('bool|int|float', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame("Value 'foo' does not match any of `bool`, `int`, `float`.", (string)$error);
        }
    }

    public function test_invalid_utf8_union_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('bool|int|float', '🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame("Value '🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄🦄…' does not match any of `bool`, `int`, `float`.", (string)$error);
        }
    }

    public function test_null_in_union_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('bool|int|float', null);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
            self::assertSame("Cannot be empty and must be filled with a value matching any of `bool`, `int`, `float`.", (string)$error);
        }
    }

    public function test_invalid_array_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<float>', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1618739163', $error->code());
            self::assertSame("Value 'foo' does not match type `array<float>`.", (string)$error);
        }
    }

    public function test_invalid_list_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('list<float>', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1618739163', $error->code());
            self::assertSame("Value 'foo' does not match type `list<float>`.", (string)$error);
        }
    }

    public function test_invalid_shaped_array_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array{foo: string}', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1618739163', $error->code());
            self::assertSame("Value 'foo' does not match type `array{foo: string}`.", (string)$error);
        }
    }

    public function test_invalid_shaped_array_with_object_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array{foo: stdClass}', 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1618739163', $error->code());
            self::assertSame("Invalid value 'foo'.", (string)$error);
        }
    }
}

final class ObjectContainingUndefinedObjectType
{
    public object $value;
}

final class ObjectContainingMixedType
{
    /** @var array{foo: string, bar: mixed} */
    public array $value;
}
