<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\IgnoreOnNormalization;
use CuyZ\Valinor\Normalizer\Exception\IgnoreOnNormalizationIsNotRegistered;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use DateTimeZone;

use function json_encode;

final class IgnoreOnNormalizationTest extends IntegrationTestCase
{
    public function test_marked_property_is_removed_from_output(): void
    {
        $object = new class () {
            public string $name = 'John Doe';

            #[IgnoreOnNormalization]
            public string $password = 's3cr3t';
        };

        $result = $this->normalizerBuilder()
            ->configureWith(new IgnoreOnNormalization())
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame(['name' => 'John Doe'], $result);
    }

    public function test_object_without_marked_property_is_left_untouched(): void
    {
        $object = new class () {
            public string $name = 'John Doe';

            public string $email = 'john.doe@example.com';
        };

        $result = $this->normalizerBuilder()
            ->configureWith(new IgnoreOnNormalization())
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ], $result);
    }

    public function test_without_registration_the_property_value_is_replaced_by_a_placeholder(): void
    {
        $object = new class () {
            public string $name = 'John Doe';

            #[IgnoreOnNormalization]
            public string $password = 's3cr3t';
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertIsArray($result);
        self::assertInstanceOf(IgnoreOnNormalization::class, $result['password']);
    }

    public function test_without_registration_the_placeholder_throws_when_cast_to_string(): void
    {
        $object = new class () {
            #[IgnoreOnNormalization]
            public string $password = 's3cr3t';
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        $this->expectException(IgnoreOnNormalizationIsNotRegistered::class);
        $this->expectExceptionMessage('The `IgnoreOnNormalization` configurator must be registered on the normalizer builder with `configureWith(new IgnoreOnNormalization())` for the attribute to take effect.');

        // @phpstan-ignore cast.string (the value is a placeholder object at runtime)
        (string)((array)$result)['password'];
    }

    public function test_without_registration_the_placeholder_throws_when_encoded_to_json(): void
    {
        $object = new class () {
            #[IgnoreOnNormalization]
            public string $password = 's3cr3t';
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        $this->expectException(IgnoreOnNormalizationIsNotRegistered::class);

        json_encode($result);
    }

    public function test_without_registration_the_json_normalizer_reports_the_missing_registration(): void
    {
        $object = new class () {
            #[IgnoreOnNormalization]
            public string $password = 's3cr3t';
        };

        $this->expectException(IgnoreOnNormalizationIsNotRegistered::class);

        $this->normalizerBuilder()
            ->normalizer(Format::json())
            ->normalize($object);
    }

    public function test_non_array_normalized_value_is_left_untouched(): void
    {
        $date = new DateTimeImmutable('2000-01-01 00:00:00', new DateTimeZone('UTC'));

        $result = $this->normalizerBuilder()
            ->configureWith(new IgnoreOnNormalization())
            ->normalizer(Format::array())
            ->normalize($date);

        self::assertIsString($result);
    }
}
