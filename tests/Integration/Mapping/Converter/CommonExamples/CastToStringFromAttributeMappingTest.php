<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class CastToStringFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_cast_to_string_converter_attribute(): void
    {
        $class = new class () {
            #[CastToString]
            public string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, 42.5);

            self::assertSame('42.5', $result->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class CastToString
{
    public function map(int|float $value): string
    {
        return (string)$value;
    }
}
