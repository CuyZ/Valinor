<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\DateInterval;

final class ExtendedDateIntervalMappingTest extends IntegrationTest
{
    public function test_extended_date_interval_is_mapped_properly(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(DateInterval::class, 'P1Y2M3DT4H5M6S');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('P1Y2M3DT4H5M6S', $result->format('P%yY%mM%dDT%hH%iM%sS'));
    }
}
