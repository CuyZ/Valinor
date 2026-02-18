<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToSnakeCase;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConvertKeysToSnakeCaseTest extends IntegrationTestCase
{
    #[DataProvider('to_snake_case_data_provider')]
    public function test_to_snake_case_converts_keys(string $inputKey, string $expectedValue): void
    {
        $class = new class () {
            public string $some_value;
        };

        try {
            $result = $this->mapperBuilder()
                ->configureWith(new ConvertKeysToSnakeCase())
                ->mapper()
                ->map($class::class, [$inputKey => $expectedValue]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($expectedValue, $result->some_value);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function to_snake_case_data_provider(): iterable
    {
        yield 'from camelCase' => ['someValue', 'foo'];
        yield 'from PascalCase' => ['SomeValue', 'bar'];
        yield 'already snake_case' => ['some_value', 'baz'];
        yield 'from kebab-case' => ['some-value', 'fiz'];
    }

    public function test_to_snake_case_converts_multiple_keys(): void
    {
        $class = new class () {
            public string $some_value;

            public string $other_value;
        };

        try {
            $result = $this->mapperBuilder()
                ->configureWith(new ConvertKeysToSnakeCase())
                ->mapper()
                ->map($class::class, ['someValue' => 'foo', 'otherValue' => 'bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->some_value);
        self::assertSame('bar', $result->other_value);
    }
}
