<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Error;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

use function ini_get;
use function ini_set;

final class MappingErrorTest extends IntegrationTestCase
{
    public function test_single_tree_mapper_error_details_are_reported_in_exception_message(): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionMessage("Could not map type `string`. An error occurred at path *root*: Value array{0: 'foo'} is not a valid string.");

        $this->mapperBuilder()->mapper()->map('string', ['foo']);
    }

    public function test_several_tree_mapper_errors_count_are_reported_in_exception_message(): void
    {
        $this->expectException(MappingError::class);
        $this->expectExceptionMessage("Could not map type `array{foo: string, bar: int}` with value array{foo: 42, bar: 'some string'}. A total of 2 errors were encountered.");

        $this->mapperBuilder()->mapper()->map(
            'array{foo: string, bar: int}',
            ['foo' => 42, 'bar' => 'some string']
        );
    }

    #[DataProvider('invalid_utf8_source_data_provider')]
    public function test_invalid_utf8_source_is_reported_as_a_mapping_error(string $intlExceptions, string $type, mixed $source, string $expected): void
    {
        $oldIni = ini_get('intl.use_exceptions');

        try {
            ini_set('intl.use_exceptions', $intlExceptions);

            try {
                $this->mapperBuilder()->mapper()->map($type, $source);
                self::fail('No mapping error when one was expected');
            } catch (MappingError $exception) {
                self::assertSame($source, $exception->source());
                self::assertStringContainsString($expected, $exception->getMessage());
                self::assertSame($expected, $exception->messages()->toArray()[0]->toString());
            }
        } finally {
            ini_set('intl.use_exceptions', $oldIni);
        }
    }

    public static function invalid_utf8_source_data_provider(): array
    {
        $cases = [
            'invalid byte' => ['int', "\xB1\x31", "Value '�1' is not a valid integer."],
            'truncated sequence' => ['int', "\xE2\x82", "Value '�' is not a valid integer."],
            'valid unicode' => ['int', 'été 🦄', "Value 'été 🦄' is not a valid integer."],
            'mixed unicode and invalid byte' => ['int', "été 🦄\xB1", "Value 'été 🦄�' is not a valid integer."],
            'nested value' => ['array{id: int}', ['id' => "\xB1\x31"], "Value '�1' is not a valid integer."],
            'array value' => ['string', ['id' => "\xB1\x31"], "Value array{id: '�1'} is not a valid string."],
        ];

        $data = [];

        foreach (['0', '1'] as $intlExceptions) {
            foreach ($cases as $name => $case) {
                $data["$name / intl.use_exceptions=$intlExceptions"] = [$intlExceptions, ...$case];
            }
        }

        return $data;
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
