<?php

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class AnonymousClassMappingTest extends IntegrationTestCase
{
    public function test_map_to_string_or_anonymous_class_with_string_works_correctly(): void
    {
        $class = new class () {
            public string $foo;
            public string $bar;
        };

        $res = $this->mapperBuilder()
            ->mapper()
            ->map('string|' . $class::class, 'foo');

        self::assertSame('foo', $res);
    }
}
