<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use stdClass;

use function array_reverse;
use function str_replace;
use function strtoupper;

final class ConverterWithTemplateReturnTypeMappingTest extends IntegrationTestCase
{
    public function test_converter_is_called_only_for_target_inferred_from_template(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConverter(
                    /**
                     * @template T of numeric-string
                     * @param callable(string): T $next
                     * @return T
                     */
                    fn (string $value, callable $next) => $next(str_replace(',', '.', $value)),
                )
                ->mapper()
                ->map('array{price: numeric-string, weight: ?numeric-string, comment: string}', [
                    'price' => '19,90',
                    'weight' => '1,5',
                    'comment' => 'Fragile, handle with care',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'price' => '19.90',
            'weight' => '1.5',
            'comment' => 'Fragile, handle with care',
        ], $result);
    }

    public function test_template_is_inferred_from_union_target_members_within_its_bound(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConverter(
                    /**
                     * @template T of bool
                     * @param callable(bool): T $next
                     * @return T
                     */
                    fn (int $value, callable $next) => $next($value === 1),
                )
                ->mapper()
                ->map('bool|int', 1);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($result);
    }

    public function test_converter_with_concrete_return_type_narrower_than_target_is_called(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConverter(
                    /**
                     * @return non-empty-string
                     */
                    fn (string $value) => "#$value",
                )
                ->mapper()
                ->map('string', '');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('#', $result);
    }

    public function test_skipped_converter_does_not_prevent_next_converters_from_being_called(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConverter(
                    /**
                     * @template T of class-string
                     * @param callable(string): T $next
                     * @return T
                     */
                    fn (string $value, callable $next) => $next(['object' => stdClass::class][$value] ?? $value),
                    priority: 10,
                )
                ->registerConverter(
                    fn (string $value): string => strtoupper($value),
                )
                ->mapper()
                ->map('array{type: class-string, name: string}', [
                    'type' => 'object',
                    'name' => 'object',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'type' => stdClass::class,
            'name' => 'OBJECT',
        ], $result);
    }

    public function test_object_converter_is_not_called_for_mixed_target(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowPermissiveTypes()
                ->registerConverter(
                    /**
                     * @template T of object
                     * @param array<mixed> $value
                     * @param callable(array<mixed>): T $next
                     * @return T
                     */
                    fn (array $value, callable $next) => $next(['value' => 'foo']),
                )
                ->mapper()
                ->map('mixed', ['bar' => 'baz']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['bar' => 'baz'], $result);
    }

    public function test_converter_with_template_nested_in_return_type_is_called_only_for_inferred_target(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->allowPermissiveTypes()
                ->registerConverter(
                    /**
                     * @template T of string
                     * @param list<string> $value
                     * @param callable(list<string>): list<T> $next
                     * @return list<T>
                     */
                    fn (array $value, callable $next) => $next(array_reverse($value)),
                )
                ->mapper()
                ->map('array{strings: list<string>, values: array}', [
                    'strings' => ['foo', 'bar'],
                    'values' => ['foo', 'bar'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([
            'strings' => ['bar', 'foo'],
            'values' => ['foo', 'bar'],
        ], $result);
    }

    public function test_attribute_converter_is_called_only_for_target_inferred_from_template(): void
    {
        $class = new class () {
            #[UppercaseNonEmptyString]
            public string $string;

            /** @var non-empty-string */
            #[UppercaseNonEmptyString]
            public string $nonEmptyString;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'string' => 'foo',
                    'nonEmptyString' => 'foo',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->string);
        self::assertSame('FOO', $result->nonEmptyString);
    }
}

#[AsConverter]
#[Attribute(Attribute::TARGET_PROPERTY)]
final class UppercaseNonEmptyString
{
    /**
     * @template T of non-empty-string
     * @param callable(string): T $next
     * @return T
     */
    public function map(string $value, callable $next): string
    {
        return $next(strtoupper($value));
    }
}
