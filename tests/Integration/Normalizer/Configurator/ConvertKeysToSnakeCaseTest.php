<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\ConvertKeysToSnakeCase;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConvertKeysToSnakeCaseTest extends IntegrationTestCase
{
    /**
     * @param array<string, non-empty-string> $expectedValue
     */
    #[DataProvider('to_snake_case_data_provider')]
    public function test_to_snake_case_converts_keys(array $expectedValue, object $object): void
    {
        $result = $this->normalizerBuilder()
            ->configureWith(new ConvertKeysToSnakeCase())
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame($expectedValue, $result);
    }

    /**
     * @return iterable<string, array{array, object}>
     */
    public static function to_snake_case_data_provider(): iterable
    {
        yield 'from camelCase' => [['some_value' => 'foo'], new class () {
            public string $someValue = 'foo';
        }];

        yield 'from PascalCase' => [['some_value' => 'foo'], new class () {
            public string $SomeValue = 'foo';
        }];

        yield 'already snake_case' => [['some_value' => 'foo'], new class () {
            public string $some_value = 'foo';
        }];

        yield 'already snake_case with underscore' => [['_some_value' => 'foo'], new class () {
            public string $_some_value = 'foo';
        }];
    }
}
