<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ArrayWithScalarValuesTest extends IntegrationTestCase
{
    public function test_array_containing_scalar_type_is_parsed_properly(): void
    {
        $class = new class (['foo', 42, 12.3, true]) {
            /**
             * @param array<array-key, scalar> $foo
             */
            public function __construct(
                public array $foo,
            ) {}
        };

        $result = $this->mapperBuilder()->mapper()->map($class::class, [
            'foo' => ['foo', 42, 12.3, true],
        ]);

        self::assertSame(['foo', 42, 12.3, true], $result->foo);
    }
}
