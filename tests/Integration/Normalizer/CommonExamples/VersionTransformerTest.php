<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use PHPUnit\Framework\TestCase;

final class VersionTransformerTest extends TestCase
{
    public function test_version_transformer_works_properly(): void
    {
        $normalizeWithVersion = fn (string $version) => (new MapperBuilder())
            ->registerTransformer(
                fn (HasVersionedNormalization $object, callable $next) => $object->normalizeWithVersion($version, $next),
            )
            ->normalizer(Format::array())
            ->normalize(new class () implements HasVersionedNormalization {
                public function __construct(
                    public string $streetNumber = '221B',
                    public string $streetName = 'Baker Street',
                    public string $zipCode = 'NW1 6XE',
                    public string $city = 'London',
                ) {}

                public function normalizeWithVersion(string $version, callable $default): mixed
                {
                    return match (true) {
                        version_compare($version, '1.0', '<') => [
                            // Street number and name are merged in a single property
                            'street' => "$this->streetNumber, $this->streetName",
                            'zipCode' => $this->zipCode,
                            'city' => $this->city,
                        ],
                        default => $default(),
                    };
                }
            });

        self::assertSame([
            'street' => '221B, Baker Street',
            'zipCode' => 'NW1 6XE',
            'city' => 'London',
        ], $normalizeWithVersion('0.4'));

        self::assertSame([
            'streetNumber' => '221B',
            'streetName' => 'Baker Street',
            'zipCode' => 'NW1 6XE',
            'city' => 'London',
        ], $normalizeWithVersion('1.0'));
    }
}

interface HasVersionedNormalization
{
    public function normalizeWithVersion(string $version, callable $default): mixed;
}
