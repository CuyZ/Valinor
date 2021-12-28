<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class ArrayOfScalarMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'foo',
            42,
            1337.404,
        ];

        try {
            $result = $this->mapperBuilder->mapper()->map('string[]', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', '42', '1337.404'], $result);
    }
}
