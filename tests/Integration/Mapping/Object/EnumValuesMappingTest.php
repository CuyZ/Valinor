<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

/**
 * @requires PHP >= 8.1
 */
final class EnumValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'pureEnumWithFirstValue' => 'FOO',
            'pureEnumWithSecondValue' => 'BAR',
            'backedStringEnum' => 'foo',
            'backedIntegerEnum' => 1337,
        ];

        foreach ([EnumValues::class, EnumValuesWithConstructor::class] as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(PureEnum::FOO, $result->pureEnumWithFirstValue);
            self::assertSame(PureEnum::BAR, $result->pureEnumWithSecondValue);
            self::assertSame(BackedStringEnum::FOO, $result->backedStringEnum);
            self::assertSame(BackedIntegerEnum::BAR, $result->backedIntegerEnum);
        }
    }

    public function test_invalid_string_enum_value_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(BackedStringEnum::class, new StringableObject('foo'));
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1633093113', $error->code());
            self::assertSame('Value object(' . StringableObject::class . ") does not match any of 'foo', 'bar'.", (string)$error);
        }
    }

    public function test_invalid_integer_enum_value_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(BackedIntegerEnum::class, '42');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1633093113', $error->code());
            self::assertSame("Value '42' does not match any of 42, 1337.", (string)$error);
        }
    }
}

class EnumValues
{
    public PureEnum $pureEnumWithFirstValue;

    public PureEnum $pureEnumWithSecondValue;

    public BackedStringEnum $backedStringEnum;

    public BackedIntegerEnum $backedIntegerEnum;
}

class EnumValuesWithConstructor extends EnumValues
{
    public function __construct(
        PureEnum $pureEnumWithFirstValue,
        PureEnum $pureEnumWithSecondValue,
        BackedStringEnum $backedStringEnum,
        BackedIntegerEnum $backedIntegerEnum
    ) {
        $this->pureEnumWithFirstValue = $pureEnumWithFirstValue;
        $this->pureEnumWithSecondValue = $pureEnumWithSecondValue;
        $this->backedStringEnum = $backedStringEnum;
        $this->backedIntegerEnum = $backedIntegerEnum;
    }
}
