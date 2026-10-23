<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;

/**
 * Renames the key of a property during normalization. This is useful when the
 * name of a property in the data format differs from the one used in the PHP
 * codebase.
 *
 * ```
 * use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeyTo;
 * use CuyZ\Valinor\Normalizer\Format;
 * use CuyZ\Valinor\NormalizerBuilder;
 *
 * final readonly class Address
 * {
 *     public function __construct(
 *         public string $street,
 *         public string $zipCode,
 *         #[NormalizeKeyTo('town')]
 *         public string $city,
 *     ) {}
 * }
 *
 * $addressAsArray = (new NormalizerBuilder())
 *     ->normalizer(Format::array())
 *     ->normalize(
 *         new Address(
 *             street: '221B Baker Street',
 *             zipCode: 'NW1 6XE',
 *             city: 'London', // Key will be renamed to 'town'
 *         )
 *     );
 *
 * // [
 * //     'street' => '221B Baker Street',
 * //     'zipCode' => 'NW1 6XE',
 * //     'town' => 'London',
 * // ]
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
#[AsTransformer]
final readonly class NormalizeKeyTo
{
    public function __construct(
        /** @var non-empty-string */
        private string $key,
    ) {}

    /**
     * @return non-empty-string
     */
    public function normalizeKey(): string
    {
        return $this->key;
    }
}
