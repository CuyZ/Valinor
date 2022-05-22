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

final class EnumValuesMappingTest extends IntegrationTest
{
    /**
     * @requires PHP >= 8.1
     */
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'pureEnumWithFirstValue' => 'FOO',
            'pureEnumWithSecondValue' => 'BAR',
            'backedStringEnumWithFirstValue' => 'foo',
            'backedStringEnumWithSecondValue' => new StringableObject('bar'),
            'backedIntegerEnumWithFirstValue' => '42',
            'backedIntegerEnumWithSecondValue' => 1337,
        ];

        foreach ([EnumValues::class, EnumValuesWithConstructor::class] as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(PureEnum::FOO, $result->pureEnumWithFirstValue);
            self::assertSame(PureEnum::BAR, $result->pureEnumWithSecondValue);
            self::assertSame(BackedStringEnum::FOO, $result->backedStringEnumWithFirstValue);
            self::assertSame(BackedStringEnum::BAR, $result->backedStringEnumWithSecondValue);
            self::assertSame(BackedIntegerEnum::FOO, $result->backedIntegerEnumWithFirstValue);
            self::assertSame(BackedIntegerEnum::BAR, $result->backedIntegerEnumWithSecondValue);
        }
    }
}

class EnumValues
{
    public PureEnum $pureEnumWithFirstValue;

    public PureEnum $pureEnumWithSecondValue;

    public BackedStringEnum $backedStringEnumWithFirstValue;

    public BackedStringEnum $backedStringEnumWithSecondValue;

    public BackedIntegerEnum $backedIntegerEnumWithFirstValue;

    public BackedIntegerEnum $backedIntegerEnumWithSecondValue;
}

class EnumValuesWithConstructor extends EnumValues
{
    public function __construct(
        PureEnum $pureEnumWithFirstValue,
        PureEnum $pureEnumWithSecondValue,
        BackedStringEnum $backedStringEnumWithFirstValue,
        BackedStringEnum $backedStringEnumWithSecondValue,
        BackedIntegerEnum $backedIntegerEnumWithFirstValue,
        BackedIntegerEnum $backedIntegerEnumWithSecondValue
    ) {
        $this->pureEnumWithFirstValue = $pureEnumWithFirstValue;
        $this->pureEnumWithSecondValue = $pureEnumWithSecondValue;
        $this->backedStringEnumWithFirstValue = $backedStringEnumWithFirstValue;
        $this->backedStringEnumWithSecondValue = $backedStringEnumWithSecondValue;
        $this->backedIntegerEnumWithFirstValue = $backedIntegerEnumWithFirstValue;
        $this->backedIntegerEnumWithSecondValue = $backedIntegerEnumWithSecondValue;
    }
}
