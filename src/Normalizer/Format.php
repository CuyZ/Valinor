<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

/**
 * @api
 *
 * @template T of Normalizer
 */
final class Format
{
    /**
     * Allows a normalizer to format an input to a PHP array, containing only
     * scalar values.
     *
     * ```
     * namespace My\App;
     *
     * $normalizer = (new \CuyZ\Valinor\NormalizerBuilder())
     *     ->normalizer(\CuyZ\Valinor\Normalizer\Format::array());
     *
     * $userAsArray = $normalizer->normalize(
     *     new \My\App\User(
     *         name: 'John Doe',
     *         age: 42,
     *         country: new \My\App\Country(
     *             name: 'France',
     *             countryCode: 'FR',
     *         ),
     *     )
     * );
     *
     * // `$userAsArray` is now an array and can be manipulated much more
     * // easily, for instance to be serialized to the wanted data format.
     * //
     * // [
     * //     'name' => 'John Doe',
     * //     'age' => 42,
     * //     'country' => [
     * //         'name' => 'France',
     * //         'countryCode' => 'FR',
     * //     ],
     * // ];
     * ```
     *
     * @pure
     * @return self<ArrayNormalizer>
     */
    public static function array(): self
    {
        return new self(ArrayNormalizer::class);
    }

    /**
     * Allows a normalizer to format an input to JSON syntax.
     *
     * ```
     * namespace My\App;
     *
     * $normalizer = (new \CuyZ\Valinor\NormalizerBuilder())
     *     ->normalizer(\CuyZ\Valinor\Normalizer\Format::json());
     *
     * $userAsJson = $normalizer->normalize(
     *     new \My\App\User(
     *         name: 'John Doe',
     *         age: 42,
     *         country: new \My\App\Country(
     *             name: 'France',
     *             code: 'FR',
     *         ),
     *     )
     * );
     *
     * // `$userAsJson` is a valid JSON string representing the data:
     * // {"name":"John Doe","age":42,"country":{"name":"France","code":"FR"}}
     * ```
     *
     * @pure
     * @return self<JsonNormalizer>
     */
    public static function json(): self
    {
        return new self(JsonNormalizer::class);
    }

    /**
     * @param class-string<T> $type
     */
    private function __construct(private string $type) {}

    /**
     * @pure
     * @return class-string<T>
     */
    public function type(): string
    {
        return $this->type;
    }
}
