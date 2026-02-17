<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToSnakeCase;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RestrictKeysToSnakeCaseTest extends IntegrationTestCase
{
    public function test_allows_snake_case_keys(): void
    {
        $class = new class () {
            public string $some_value;
        };

        try {
            $result = $this->mapperBuilder()
                ->configureWith(new RestrictKeysToSnakeCase())
                ->mapper()
                ->map($class::class, ['some_value' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->some_value);
    }

    /**
     * @param non-empty-string $expectedMessage
     */
    #[DataProvider('invalid_case_data_provider')]
    public function test_rejects_invalid_key_case(string $key, string $expectedMessage): void
    {
        $class = new class () {
            public string $someValue;
        };

        try {
            $this->mapperBuilder()
                ->configureWith(new RestrictKeysToSnakeCase())
                ->mapper()
                ->map($class::class, [$key => 'foo']);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                $key => $expectedMessage,
            ]);
        }
    }

    /**
     * @return iterable<string, array{string, non-empty-string}>
     */
    public static function invalid_case_data_provider(): iterable
    {
        yield 'rejects camelCase' => [
            'someValue',
            '[invalid_key_case] Key must follow the snake_case format.',
        ];

        yield 'rejects kebab-case' => [
            'some-value',
            '[invalid_key_case] Key must follow the snake_case format.',
        ];

        yield 'rejects PascalCase' => [
            'SomeValue',
            '[invalid_key_case] Key must follow the snake_case format.',
        ];
    }
}
