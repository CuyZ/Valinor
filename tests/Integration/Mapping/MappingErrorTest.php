<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class MappingErrorTest extends IntegrationTestCase
{
    public function test_single_tree_mapper_error_details_are_reported_in_exception_message(): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionCode(1617193185);
        $this->expectExceptionMessage("Could not map type `string`. An error occurred at path *root*: Value array{0: 'foo'} is not a valid string.");

        $this->mapperBuilder()->mapper()->map('string', ['foo']);
    }

    public function test_several_tree_mapper_errors_count_are_reported_in_exception_message(): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionCode(1617193185);
        $this->expectExceptionMessage("Could not map type `array{foo: string, bar: int}` with value array{foo: 42, bar: 'some string'}. A total of 2 errors were encountered.");

        $this->mapperBuilder()->mapper()->map(
            'array{foo: string, bar: int}',
            ['foo' => 42, 'bar' => 'some string']
        );
    }

    public function test_single_argument_mapper_error_details_are_reported_in_exception_message(): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionCode(1671115362);

        $this->mapperBuilder()->argumentsMapper()->mapArguments(fn (string $foo) => $foo, 42);
    }

    public function test_type_and_source_are_accessible_from_the_mapping_error(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('array<string>', ['foo', 42, 'bar']);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertSame('array<string>', $exception->type());
            self::assertSame(['foo', 42, 'bar'], $exception->source());
        }
    }

    public function test_type_and_source_are_accessible_from_the_arguments_mapping_error(): void
    {
        try {
            $this->mapperBuilder()
                ->argumentsMapper()
                ->mapArguments(fn (string $foo) => $foo, 42);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertSame('array{foo: string}', $exception->type());
            self::assertSame(42, $exception->source());
        }
    }
}
