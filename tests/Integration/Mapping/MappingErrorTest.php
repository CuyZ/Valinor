<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class MappingErrorTest extends IntegrationTest
{
    public function test_first_error_is_reported_in_exception_message(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(
                    'array{foo: string, bar: int}',
                    ['foo' => [], 'bar' => []]
                );
            self::fail();
        } catch (MappingError $exception) {
            self::assertStringContainsString('Path: foo', $exception->getMessage());
            self::assertStringContainsString('Given value: array (empty)', $exception->getMessage());
            self::assertStringContainsString('`string`', $exception->getMessage());

            self::assertStringNotContainsString('Path: bar', $exception->getMessage());
        }
    }
}
