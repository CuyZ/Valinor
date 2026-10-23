<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\NormalizeToSingleValue;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class NormalizeToSingleValueTest extends IntegrationTestCase
{
    public function test_single_property_object_is_flattened(): void
    {
        $object = new class () {
            public string $email = 'john.doe@example.com';
        };

        $result = $this->normalizerBuilder()
            ->configureWith(new NormalizeToSingleValue())
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame('john.doe@example.com', $result);
    }

    public function test_object_with_several_properties_is_left_untouched(): void
    {
        $object = new class () {
            public string $name = 'John Doe';

            public string $email = 'john.doe@example.com';
        };

        $result = $this->normalizerBuilder()
            ->configureWith(new NormalizeToSingleValue())
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ], $result);
    }

    public function test_attribute_flattens_only_targeted_property(): void
    {
        $email = new class () {
            public string $email = 'john.doe@example.com';
        };

        $object = new class ($email) {
            public string $name = 'John Doe';

            public function __construct(
                #[NormalizeToSingleValue]
                public object $email,
            ) {}
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ], $result);
    }

    public function test_attribute_flattens_targeted_class(): void
    {
        $object = new #[NormalizeToSingleValue] class () {
            public string $email = 'john.doe@example.com';
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame('john.doe@example.com', $result);
    }
}
