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
     * ```php
     * namespace My\App;
     *
     * $normalizer = (new \CuyZ\Valinor\MapperBuilder())
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
     * // `$userAsArray` is now an array and can be manipulated much more easily, for
     * // instance to be serialized to the wanted data format.
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
     * @return self<ArrayNormalizer>
     */
    public static function array(): self
    {
        return new self(ArrayNormalizer::class);
    }

    /**
     * @param class-string<T> $type
     */
    private function __construct(private string $type) {}

    /**
     * @return class-string<T>
     */
    public function type(): string
    {
        return $this->type;
    }
}
