<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToPascalCase;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;

final class NormalizeKeysToPascalCaseTest extends IntegrationTestCase
{
    /**
     * @param array<string, non-empty-string> $expectedValue
     */
    #[DataProvider('to_pascal_case_data_provider')]
    public function test_to_pascal_case_converts_keys(array $expectedValue, object $object): void
    {
        $result = $this->normalizerBuilder()
            ->configureWith(new NormalizeKeysToPascalCase())
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame($expectedValue, $result);
    }

    public function test_to_pascal_case_attribute_converts_keys_of_annotated_class(): void
    {
        $object = new #[NormalizeKeysToPascalCase] class () {
            public string $some_value = 'foo';
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame(['SomeValue' => 'foo'], $result);
    }

    public function test_to_pascal_case_attribute_targets_only_annotated_class(): void
    {
        $annotated = new #[NormalizeKeysToPascalCase] class () {
            public string $postal_code = 'NW1 6XE';
        };

        $object = new class ($annotated) {
            public function __construct(
                public object $address,
                public string $user_name = 'John Doe',
            ) {}
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        // Only the annotated nested class is converted; the enclosing object
        // keeps its original `snake_case` keys.
        self::assertSame([
            'address' => ['PostalCode' => 'NW1 6XE'],
            'user_name' => 'John Doe',
        ], $result);
    }

    public function test_to_pascal_case_leaves_non_array_normalized_value_untouched(): void
    {
        // Some objects (e.g. a `DateTimeInterface`) normalize to a scalar; the
        // configurator must return that value as-is instead of mangling it.
        $date = new DateTimeImmutable('2000-01-01T00:00:00+00:00');

        $result = $this->normalizerBuilder()
            ->configureWith(new NormalizeKeysToPascalCase())
            ->normalizer(Format::array())
            ->normalize($date);

        self::assertSame('2000-01-01T00:00:00.000000+00:00', $result);
    }

    /**
     * @return iterable<string, array{array, object}>
     */
    public static function to_pascal_case_data_provider(): iterable
    {
        yield 'from snake_case' => [['SomeValue' => 'foo'], new class () {
            public string $some_value = 'foo';
        }];

        yield 'from camelCase' => [['SomeValue' => 'foo'], new class () {
            public string $someValue = 'foo';
        }];

        yield 'already PascalCase' => [['SomeValue' => 'foo'], new class () {
            public string $SomeValue = 'foo';
        }];

        yield 'from snake_case with leading underscore' => [['SomeValue' => 'foo'], new class () {
            public string $_some_value = 'foo';
        }];
    }
}
