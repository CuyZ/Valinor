<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\NormalizerBuilder;
use DateTimeInterface;

/**
 * Convert a `DateTimeInterface` to a string using the given format.
 *
 *  ```
 *  use CuyZ\Valinor\Normalizer\Configurator\ConvertDateTime;
 *  use CuyZ\Valinor\Normalizer\Format;
 *  use CuyZ\Valinor\NormalizerBuilder;
 *
 *  $userAsArray = (new NormalizerBuilder())
 *      ->configureWith(new ConvertDateTime(\DateTimeInterface::ATOM))
 *      ->normalizer(Format::array())
 *      ->normalize($user);
 *
 *  // [
 *  //     'name' => 'Jane Doe',
 *  //     'createdAt' => '2000-01-01T00:00:00+00:00',
 *  // ]
 *  ```
 *
 * This class can also be used as an attribute:
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
 *         #[ConvertDateTime(\DateTimeInterface::ATOM)]
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
    /**
     * @param non-empty-string $format
     */
    public function __construct(private string $format) {}

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
