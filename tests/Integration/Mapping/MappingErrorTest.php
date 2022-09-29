<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class MappingErrorTest extends IntegrationTest
{
    public function test_single_error_details_are_reported_in_exception_message(): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionCode(1617193185);
        $this->expectExceptionMessage("Could not map type `string`. An error occurred at path *root*: Value array{0: 'foo'} is not a valid string.");

        (new MapperBuilder())->mapper()->map('string', ['foo']);
    }

    public function test_several_errors_count_are_reported_in_exception_message(): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionCode(1617193185);
        $this->expectExceptionMessage("Could not map type `array{foo: string, bar: int}` with value array{foo: 42, bar: 'some string'}. A total of 2 errors were encountered.");

        (new MapperBuilder())->mapper()->map(
            'array{foo: string, bar: int}',
            ['foo' => 42, 'bar' => 'some string']
        );
    }
}
