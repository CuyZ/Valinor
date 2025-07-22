<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function lcfirst;
use function str_replace;
use function ucwords;

final class ArrayKeysToCamelCaseMappingTest extends IntegrationTestCase
{
    public function test_can_use_camel_case_keys_converter(): void
    {
        $class = new class () {
            public string $firstName;

            public string $lastName;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerConverter(function (array $value, callable $next): object {
                    $transformed = [];

                    foreach ($value as $key => $item) {
                        $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));

                        $transformed[$camelCaseKey] = $item;
                    }

                    /** @var callable(array<mixed>): object $next */
                    return $next($transformed);
                })
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
