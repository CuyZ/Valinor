<?php

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class AnonymousClassMappingTest extends IntegrationTest
{
    public function test_map_to_string_or_anonymous_class_with_string_works_correctly(): void
    {
        $class = new class () {
            public string $foo;
            public string $bar;
        };

        $res = (new MapperBuilder())
            ->mapper()
            ->map('string|' . $class::class, 'foo');

        self::assertSame('foo', $res);
    }
}
