<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source\Modifier;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Modifier\CamelCaseKeys;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\ObjectWithSubProperties;
use PHPUnit\Framework\Attributes\DataProvider;

final class CamelCaseKeysMappingTest extends IntegrationTestCase
{
    /**
     * @param iterable<mixed> $source
     */
    #[DataProvider('sources_are_mapped_properly_data_provider')]
    public function test_sources_are_mapped_properly(iterable $source): void
    {
        try {
            $object = (new MapperBuilder())->mapper()->map(ObjectWithSubProperties::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someValue->someNestedValue);
        self::assertSame('bar', $object->someValue->someOtherNestedValue);
        self::assertSame('foo2', $object->someOtherValue->someNestedValue);
        self::assertSame('bar2', $object->someOtherValue->someOtherNestedValue);
    }

    public static function sources_are_mapped_properly_data_provider(): iterable
    {
        yield 'underscore' => [
            new CamelCaseKeys([
                'some_value' => [
                    'some_nested_value' => 'foo',
                    'some_other_nested_value' => 'bar',
                ],
                'some_other_value' => [
                    'some_nested_value' => 'foo2',
                    'some_other_nested_value' => 'bar2',
                ],
            ]),
        ];

        yield 'dash' => [
            new CamelCaseKeys([
                'some-value' => [
                    'some-nested-value' => 'foo',
                    'some-other-nested-value' => 'bar',
                ],
                'some-other-value' => [
                    'some-nested-value' => 'foo2',
                    'some-other-nested-value' => 'bar2',
                ],
            ]),
        ];

        yield 'space' => [
            new CamelCaseKeys([
                'some-value' => [
                    'some nested value' => 'foo',
                    'some other nested value' => 'bar',
                ],
                'some other value' => [
                    'some nested value' => 'foo2',
                    'some other nested value' => 'bar2',
                ],
            ]),
        ];

        yield 'existing key should not be overridden' => [
            new CamelCaseKeys([
                'someValue' => [
                    'someNestedValue' => 'foo',
                    'someOtherNestedValue' => 'bar',
                    'some nested value' => 'incorrect',
                    'some other nested value' => 'incorrect',
                ],
                'someOtherValue' => [
                    'someNestedValue' => 'foo2',
                    'someOtherNestedValue' => 'bar2',
                ],
            ]),
        ];

        yield 'existing underscore key should not be overridden' => [
            new CamelCaseKeys([
                'someValue' => [
                    'some nested value' => 'foo',
                    'some other nested value' => 'bar',
                    'someNestedValue' => 'incorrect',
                    'someOtherNestedValue' => 'incorrect',
                ],
                'someOtherValue' => [
                    'someNestedValue' => 'foo2',
                    'someOtherNestedValue' => 'bar2',
                ],
            ]),
        ];
    }
}
