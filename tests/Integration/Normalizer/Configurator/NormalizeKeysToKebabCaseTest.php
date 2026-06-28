<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToKebabCase;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;

final class NormalizeKeysToKebabCaseTest extends IntegrationTestCase
{
    /**
     * @param array<string, non-empty-string> $expectedValue
     */
    #[DataProvider('to_kebab_case_data_provider')]
    public function test_to_kebab_case_converts_keys(array $expectedValue, object $object): void
    {
        $result = $this->normalizerBuilder()
            ->configureWith(new NormalizeKeysToKebabCase())
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame($expectedValue, $result);
    }

    public function test_to_kebab_case_attribute_converts_keys_of_annotated_class(): void
    {
        $object = new #[NormalizeKeysToKebabCase] class () {
            public string $someValue = 'foo';
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame(['some-value' => 'foo'], $result);
    }

    public function test_to_kebab_case_attribute_targets_only_annotated_class(): void
    {
        $annotated = new #[NormalizeKeysToKebabCase] class () {
            public string $postalCode = 'NW1 6XE';
        };

        $object = new class ($annotated) {
            public function __construct(
                public object $address,
                public string $userName = 'John Doe',
            ) {}
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        // Only the annotated nested class is converted; the enclosing object
        // keeps its original `camelCase` keys.
        self::assertSame([
            'address' => ['postal-code' => 'NW1 6XE'],
            'userName' => 'John Doe',
        ], $result);
    }

    public function test_to_kebab_case_leaves_non_array_normalized_value_untouched(): void
    {
        // Some objects (e.g. a `DateTimeInterface`) normalize to a scalar; the
        // configurator must return that value as-is instead of mangling it.
        $date = new DateTimeImmutable('2000-01-01T00:00:00+00:00');

        $result = $this->normalizerBuilder()
            ->configureWith(new NormalizeKeysToKebabCase())
            ->normalizer(Format::array())
            ->normalize($date);

        self::assertSame('2000-01-01T00:00:00.000000+00:00', $result);
    }

    /**
     * @return iterable<string, array{array, object}>
     */
    public static function to_kebab_case_data_provider(): iterable
    {
        yield 'from camelCase' => [['some-value' => 'foo'], new class () {
            public string $someValue = 'foo';
        }];

        yield 'from PascalCase' => [['some-value' => 'foo'], new class () {
            public string $SomeValue = 'foo';
        }];

        yield 'from snake_case' => [['some-value' => 'foo'], new class () {
            public string $some_value = 'foo';
        }];

        yield 'from camelCase with multiple words' => [['number-of-items' => 'foo'], new class () {
            public string $numberOfItems = 'foo';
        }];
    }
}
