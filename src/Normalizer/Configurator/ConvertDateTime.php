<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\NormalizerBuilder;
use DateTimeInterface;

/**
 * Converts a `DateTimeInterface` to a string using the given format.
 *
 * This class can be used either as a configurator for global usage or as an
 * attribute to target a specific property.
 *
 * Global usage as a configurator
 * ------------------------------
 *
 *  ```
 *  use CuyZ\Valinor\Normalizer\Configurator\ConvertDateTime;
 *  use CuyZ\Valinor\Normalizer\Format;
 *  use CuyZ\Valinor\NormalizerBuilder;
 *
 *  $userAsArray = (new NormalizerBuilder())
 *      // All `DateTimeInterface` will be converted to this format
 *      ->configureWith(new ConvertDateTime(DATE_ATOM))
 *      ->normalizer(Format::array())
 *      ->normalize($user);
 *
 *  // [
 *  //     'name' => 'Jane Doe',
 *  //     'createdAt' => '2000-01-01T00:00:00+00:00',
 *  // ]
 *  ```
 *
 * Local usage as an attribute
 * ---------------------------
 *
 * ```
 * use CuyZ\Valinor\Normalizer\Configurator\ConvertDateTime;
 * use CuyZ\Valinor\Normalizer\Format;
 * use CuyZ\Valinor\NormalizerBuilder;
 *
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[ConvertDateTime(DATE_ATOM)]
 *         public DateTimeInterface $createdAt,
 *     ) {}
 * }
 *
 * $userAsArray = (new NormalizerBuilder())
 *     ->normalizer(Format::array())
 *     ->normalize($user);
 *
 * // [
 * //     'name' => 'Jane Doe',
 * //     'createdAt' => '2000-01-01T00:00:00+00:00',
 * // ]
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
#[AsTransformer]
final readonly class ConvertDateTime implements NormalizerBuilderConfigurator
{
    public function __construct(
        /** @var non-empty-string */
        private string $format
    ) {}

    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder->registerTransformer($this->normalize(...));
    }

    /**
     * @return non-empty-string
     */
    public function normalize(DateTimeInterface $date): string
    {
        return $date->format($this->format);
    }
}
