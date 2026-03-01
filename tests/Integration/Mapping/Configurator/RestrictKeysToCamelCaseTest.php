<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToCamelCase;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RestrictKeysToCamelCaseTest extends IntegrationTestCase
{
    public function test_allows_camel_case_keys(): void
    {
        $class = new class () {
            public string $someValue;
        };

        try {
            $result = $this->mapperBuilder()
                ->configureWith(new RestrictKeysToCamelCase())
                ->mapper()
                ->map($class::class, ['someValue' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->someValue);
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
                ->configureWith(new RestrictKeysToCamelCase())
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
        yield 'rejects snake_case' => [
            'some_value',
            '[invalid_key_case] Key must follow the camelCase format.',
        ];

        yield 'rejects kebab-case' => [
            'some-value',
            '[invalid_key_case] Key must follow the camelCase format.',
        ];

        yield 'rejects PascalCase' => [
            'SomeValue',
            '[invalid_key_case] Key must follow the camelCase format.',
        ];
    }
}
