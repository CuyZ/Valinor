<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class RenameKeysMappingTest extends IntegrationTestCase
{
    public function test_can_use_rename_keys_converter(): void
    {
        $class = new class () {
            public string $city;
            public string $zipCode;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerConverter(
                    /**
                     * @template T of object
                     * @param array<mixed> $value
                     * @param callable(array<mixed>): T $next
                     * @return T
                     */
                    function (array $value, callable $next): object {
                        $mapping = [
                            'town' => 'city',
                            'postalCode' => 'zipCode',
                        ];

                        $renamed = [];

                        foreach ($value as $key => $item) {
                            $renamed[$mapping[$key] ?? $key] = $item;
                        }

                        /** @var callable(array<mixed>): object $next */
                        return $next($renamed);
                    }
                )
                ->mapper()
                ->map($class::class, [
                    'town' => 'Lyon',
                    'postalCode' => '69000',
                ]);

            self::assertSame('Lyon', $result->city);
            self::assertSame('69000', $result->zipCode);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}
