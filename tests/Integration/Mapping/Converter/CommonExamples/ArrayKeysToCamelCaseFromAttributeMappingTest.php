<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function lcfirst;
use function str_replace;
use function ucwords;

final class ArrayKeysToCamelCaseFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_camel_case_keys_converter_attribute(): void
    {
        $class = new #[CamelCaseKeys] class () {
            public string $firstName;

            public string $lastName;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]);

            self::assertSame('John', $result->firstName);
            self::assertSame('Doe', $result->lastName);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class CamelCaseKeys
{
    /**
     * @template T of object
     * @param array<mixed> $value
     * @param callable(array<mixed>): T $next
     * @return T
     */
    public function map(array $value, callable $next): object
    {
        $transformed = [];

        foreach ($value as $key => $item) {
            $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));

            $transformed[$camelCaseKey] = $item;
        }

        return $next($transformed);
    }
}
