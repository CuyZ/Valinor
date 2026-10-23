<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeyTo;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class NormalizeKeyToTest extends IntegrationTestCase
{
    public function test_property_key_is_renamed(): void
    {
        $object = new class () {
            public string $street = '221B Baker Street';
            public string $zipCode = 'NW1 6XE';
            #[NormalizeKeyTo('town')]
            public string $city = 'London';
        };

        $result = $this->normalizerBuilder()
            ->normalizer(Format::array())
            ->normalize($object);

        self::assertSame([
            'street' => '221B Baker Street',
            'zipCode' => 'NW1 6XE',
            'town' => 'London',
        ], $result);
    }
}
