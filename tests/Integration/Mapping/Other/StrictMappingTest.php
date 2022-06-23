<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Exception\PermissiveTypeNotAllowed;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\ClassWithBackedStringEnum;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Utility\PermissiveTypeFound;
use stdClass;

use function get_class;

final class StrictMappingTest extends IntegrationTest
{
    public function test_missing_value_throws_exception(): void
    {
        $class = new class () {
            public string $foo;

            public string $bar;
        };

        try {
            (new MapperBuilder())->mapper()->map(get_class($class), [
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

        (new MapperBuilder())->mapper()->map('object', new stdClass());
    }

    public function test_map_to_object_containing_undefined_object_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeNotAllowed::class);
        $this->expectExceptionCode(1655389255);
        $this->expectExceptionMessage('Error for `value` in `' . ObjectContainingUndefinedObjectType::class . ' (properties)`: Type `object` is too permissive.');

        (new MapperBuilder())->mapper()->map(ObjectContainingUndefinedObjectType::class, ['value' => new stdClass()]);
    }

    public function test_map_to_type_containing_mixed_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeFound::class);
        $this->expectExceptionCode(1655231817);
        $this->expectExceptionMessage('Type `mixed` in `array{foo: string, bar: mixed}` is too permissive.');

        (new MapperBuilder())->mapper()->map('array{foo: string, bar: mixed}', ['foo' => 'foo', 'bar' => 42]);
    }

    public function test_map_to_object_containing_mixed_type_throws_exception(): void
    {
        $this->expectException(PermissiveTypeNotAllowed::class);
        $this->expectExceptionCode(1655389255);
        $this->expectExceptionMessage('Error for `value` in `' . ObjectContainingMixedType::class . ' (properties)`: Type `mixed` in `array{foo: string, bar: mixed}` is too permissive.');

        (new MapperBuilder())->mapper()->map(ObjectContainingMixedType::class, ['value' => 'foo']);
    }

    public function test_superfluous_key_for_single_scalar_node_throws_correct_exceptions(): void
    {
        $class = new class () {
            public string $value;
        };

        try {
            (new MapperBuilder())->mapper()->map(get_class($class), [
                'unexpectedKey' => 'foo',
            ]);
        } catch (MappingError $exception) {
            $errorA = $exception->node()->messages()[0];
            $errorB = $exception->node()->children()['value']->messages()[0];

            self::assertSame('1655149208', $errorA->code());
            self::assertSame('Unexpected key(s) `unexpectedKey`, expected `value`.', (string)$errorA);

            self::assertSame('1655449641', $errorB->code());
            self::assertSame('Cannot be empty and must be filled with a value matching type `string`.', (string)$errorB);
        }
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_superfluous_key_for_single_enum_node_throws_correct_exceptions(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(ClassWithBackedStringEnum::class, [
                'unexpectedKey' => 'foo',
            ]);
        } catch (MappingError $exception) {
            $errorA = $exception->node()->messages()[0];
            $errorB = $exception->node()->children()['value']->messages()[0];

            self::assertSame('1655149208', $errorA->code());
            self::assertSame('Unexpected key(s) `unexpectedKey`, expected `value`.', (string)$errorA);

            self::assertSame('1655449641', $errorB->code());
            self::assertSame('Cannot be empty and must be filled with a value matching type `?`.', (string)$errorB);
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
