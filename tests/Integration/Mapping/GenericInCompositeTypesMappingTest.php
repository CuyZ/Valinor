<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GenericInCompositeTypesMappingTest extends IntegrationTestCase
{
    #[DataProvider('generic_can_be_used_in_composite_types_data_provider')]
    public function test_generic_can_be_used_in_composite_types(mixed $source, string $signature): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($signature, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        /** @var object{value: mixed} $result */
        self::assertSame($source, $result->value);
    }

    public static function generic_can_be_used_in_composite_types_data_provider(): iterable
    {
        yield 'class-string subtype generic' => [
            'source' => 'stdClass',
            'signature' =>
                (new /** @template T */ class () {
                    /** @var class-string<T> */
                    public $value;
                })::class . '<stdClass>',
        ];

        yield 'array key generic' => [
            'source' => [42 => 'foo'],
            'signature' =>
                (new /** @template T of array-key */ class () {
                    /** @var array<T, string> */
                    public $value;
                })::class . '<int>',
        ];

        yield 'array subtype generic' => [
            'source' => [42 => 'foo', 1337 => 'bar'],
            'signature' =>
                (new /** @template T */ class () {
                    /** @var array<int, T> */
                    public $value;
                })::class . '<string>',
        ];

        yield 'non-empty-array key generic' => [
            'source' => [42 => 'foo'],
            'signature' =>
                (new /** @template T of array-key */ class () {
                    /** @var non-empty-array<T, string> */
                    public $value;
                })::class . '<int>',
        ];

        yield 'non-empty-array subtype generic' => [
            'source' => [42 => 'foo', 1337 => 'bar'],
            'signature' =>
                (new /** @template T */ class () {
                    /** @var non-empty-array<int, T> */
                    public $value;
                })::class . '<string>',
        ];

        yield 'iterable key generic' => [
            'source' => [42 => 'foo'],
            'signature' =>
                (new /** @template T of array-key */ class () {
                    /** @var iterable<T, string> */
                    public $value;
                })::class . '<int>',
        ];

        yield 'iterable subtype generic' => [
            'source' => [42 => 'foo', 1337 => 'bar'],
            'signature' =>
                (new /** @template T */ class () {
                    /** @var iterable<int, T> */
                    public $value;
                })::class . '<string>',
        ];

        yield 'list subtype generic' => [
            'source' => ['foo', 'bar'],
            'signature' =>
                (new /** @template T */ class () {
                    /** @var list<T> */
                    public $value;
                })::class . '<string>',
        ];

        yield 'non-empty-list subtype generic' => [
            'source' => ['foo', 'bar'],
            'signature' =>
                (new /** @template T */ class () {
                    /** @var non-empty-list<T> */
                    public $value;
                })::class . '<string>',
        ];

        yield 'shaped array subtype generic' => [
            'source' => ['someString' => 'foo', 'someGeneric' => 'bar'],
            'signature' =>
                (new /** @template T */ class () {
                    /** @var array{someString: string, someGeneric: T} */
                    public $value;
                })::class . '<string>',
        ];

        yield 'shaped array unsealed generic' => [
            'source' => ['someString' => 'foo', 'someUnsealedValue' => 42, 'anotherUnsealedValue' => 1337],
            'signature' =>
                (new /** @template T */ class () {
                    // @phpstan-ignore-next-line PHPStan does not support unsealed array types
                    /** @var array{someString: string, ...array<T>} */ // @phpstan-ignore-next-line PHPStan does not support unsealed array types
                    public $value;
                })::class . '<int>',
        ];

        yield 'union subtype generic' => [
            'source' => 42,
            'signature' =>
                (new /** @template T */ class () {
                    /** @var string|T */
                    public $value;
                })::class . '<int>',
        ];
    }
}
