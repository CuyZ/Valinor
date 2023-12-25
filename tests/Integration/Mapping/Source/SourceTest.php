<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\ObjectWithSubProperties;
use IteratorAggregate;
use Traversable;

final class SourceTest extends IntegrationTest
{
    /**
     * @dataProvider sources_are_mapped_properly_data_provider
     *
     * @param iterable<mixed> $source
     */
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

    public function sources_are_mapped_properly_data_provider(): iterable
    {
        yield 'Iterable class' => [
            Source::iterable(new class () implements IteratorAggregate {
                public function getIterator(): Traversable
                {
                    yield from [
                        'someValue' => [
                            'someNestedValue' => 'foo',
                            'someOtherNestedValue' => 'bar',
                        ],
                        'someOtherValue' => [
                            'someNestedValue' => 'foo2',
                            'someOtherNestedValue' => 'bar2',
                        ],
                    ];
                }
            }),
        ];

        yield 'Array' => [
            Source::array([
                'someValue' => [
                    'someNestedValue' => 'foo',
                    'someOtherNestedValue' => 'bar',
                ],
                'someOtherValue' => [
                    'someNestedValue' => 'foo2',
                    'someOtherNestedValue' => 'bar2',
                ],
            ]),
        ];

        yield 'JSON' => [
            Source::json('{"someValue": {"someNestedValue": "foo", "someOtherNestedValue": "bar"}, "someOtherValue": {"someNestedValue": "foo2", "someOtherNestedValue": "bar2"}}'),
        ];

        if (extension_loaded('yaml')) {
            yield 'YAML' => [
                Source::yaml("someValue:\n someNestedValue: foo\n someOtherNestedValue: bar\nsomeOtherValue:\n someNestedValue: foo2\n someOtherNestedValue: bar2"),
            ];
        }

        yield 'Camel case' => [
            Source::array([
                'some_value' => [
                    'some_nested_value' => 'foo',
                    'some-other-nested-value' => 'bar',
                ],
                'some other value' => [
                    'some nested value' => 'foo2',
                    'some-other-nested-value' => 'bar2',
                ],
            ])->camelCaseKeys(),
        ];

        yield 'Path mapping' => [
            Source::array([
                'A1' => [
                    'B' => 'foo',
                    'C' => 'bar',
                ],
                'A2' => [
                    'B' => 'foo2',
                    'C' => 'bar2',
                ],
            ])->map([
                'A1' => 'someValue',
                'A2' => 'someOtherValue',
                '*.B' => 'someNestedValue',
                '*.C' => 'someOtherNestedValue',
            ]),
        ];

        yield 'Camel case the path mapping' => [
            Source::array([
                'level-one' => [
                    'sub-level-one' => 'foo',
                    'sub-level-two' => 'bar',
                ],
                'level-two' => [
                    'sub-level-one' => 'foo2',
                    'sub-level-two' => 'bar2',
                ],
            ])->camelCaseKeys()->map([
                'levelOne' => 'someValue',
                'levelTwo' => 'someOtherValue',
                '*.subLevelOne' => 'someNestedValue',
                '*.subLevelTwo' => 'someOtherNestedValue',
            ]),
        ];

        yield 'Path mapping then camel case' => [
            Source::array([
                'levelOne' => [
                    'subLevelOne' => 'foo',
                    'subLevelTwo' => 'bar',
                ],
                'levelTwo' => [
                    'subLevelOne' => 'foo2',
                    'subLevelTwo' => 'bar2',
                ],
            ])->map([
                'levelOne' => 'some-value',
                'levelTwo' => 'some other value',
                '*.subLevelOne' => 'some_nested_value',
                '*.subLevelTwo' => 'some other nested value',
            ])->camelCaseKeys(),
        ];
    }
}
