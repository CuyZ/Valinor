<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class NonSequentialListAllowedMappingTest extends IntegrationTestCase
{
    public function test_associative_array_is_converted_to_list(): void
    {
        try {
            $result = $this
                ->mapperBuilder()
                ->allowNonSequentialList()
                ->mapper()
                ->map('list<string>', [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]);

            self::assertSame(['foo', 'bar'], $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}
