<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function array_values;

final class ArrayToListFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_array_to_list_converter_attribute(): void
    {
        $class = new class () {
            /** @var non-empty-list<string> */
            #[ArrayToList] public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['foo' => 'foo', 'bar' => 'bar']);

            self::assertSame(['foo', 'bar'], $result->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class ArrayToList
{
    /**
     * @template T
     * @param non-empty-array<T> $value
     * @return non-empty-list<T>
     */
    public function map(array $value): array
    {
        return array_values($value);
    }
}
