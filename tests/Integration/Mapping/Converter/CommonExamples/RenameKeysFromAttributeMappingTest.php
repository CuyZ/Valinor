<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class RenameKeysFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_rename_keys_converter_attribute(): void
    {
        $class = new #[RenameKeys([ 'town' => 'city', 'postalCode' => 'zipCode', ])] class () {
            public string $city;
            public string $zipCode;
        };

        try {
            $result = $this->mapperBuilder()
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

#[Attribute, AsConverter]
final class RenameKeys
{
    public function __construct(
        /** @var non-empty-array<non-empty-string, non-empty-string> */
        private array $mapping,
    ) {}

    /**
     * @param array<mixed> $value
     * @param callable(array<mixed>): object $next
     */
    public function map(array $value, callable $next): object
    {
        $renamed = [];

        foreach ($value as $key => $item) {
            $renamed[$this->mapping[$key] ?? $key] = $item;
        }

        return $next($renamed);
    }
}
