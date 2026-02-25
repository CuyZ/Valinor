<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConvertKeysToCamelCaseTest extends IntegrationTestCase
{
    #[DataProvider('to_camel_case_data_provider')]
    public function test_to_camel_case_converts_keys(string $inputKey, string $expectedValue): void
    {
        $class = new class () {
            public string $someValue;
        };

        try {
            $result = $this->mapperBuilder()
                ->configureWith(new ConvertKeysToCamelCase())
                ->mapper()
                ->map($class::class, [$inputKey => $expectedValue]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($expectedValue, $result->someValue);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function to_camel_case_data_provider(): iterable
    {
        yield 'already camelCase' => ['someValue', 'foo'];
        yield 'from PascalCase' => ['SomeValue', 'bar'];
        yield 'from snake_case' => ['some_value', 'baz'];
        yield 'from kebab-case' => ['some-value', 'fiz'];
    }

    public function test_to_camel_case_converts_multiple_keys(): void
    {
        $class = new class () {
            public string $someValue;

            public string $otherValue;
        };

        try {
            $result = $this->mapperBuilder()
                ->configureWith(new ConvertKeysToCamelCase())
                ->mapper()
                ->map($class::class, ['some_value' => 'foo', 'other_value' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->someValue);
        self::assertSame('bar', $result->otherValue);
    }
}
