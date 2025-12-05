<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class CircularObjectMappingTest extends IntegrationTestCase
{
    public function test_can_recursively_map_nested_property_referencing_self(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(ObjectWithCircularReference::class, [
                    'value' => 'foo',
                    'children' => [
                        ['value' => 'bar'],
                        [
                            'value' => 'baz',
                            'children' => [
                                ['value' => 'fiz'],
                            ],
                        ],
                    ],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('fiz', $result->children[1]->children[0]->value);
    }
}

final class ObjectWithCircularReference
{
    public string $value;

    /** @var list<self> */
    public array $children = [];
}
