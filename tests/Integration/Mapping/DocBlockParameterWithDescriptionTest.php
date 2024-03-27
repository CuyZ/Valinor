<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class DocBlockParameterWithDescriptionTest extends IntegrationTestCase
{
    public function test_parameter_doc_block_description_containing_name_of_other_parameter_is_parsed_properly(): void
    {
        $class = new class ('foo', 42) {
            /**
             * @param non-empty-string $foo Some description containing $bar
             *      which is the next parameter name
             */
            public function __construct(
                public string $foo,
                public int $bar,
            ) {}
        };

        $result = $this->mapperBuilder()->mapper()->map($class::class, [
            'foo' => 'foo',
            'bar' => 42,
        ]);

        self::assertSame('foo', $result->foo);
        self::assertSame(42, $result->bar);
    }
}
