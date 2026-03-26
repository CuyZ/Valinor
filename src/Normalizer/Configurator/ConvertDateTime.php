<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use CuyZ\Valinor\NormalizerBuilder;
use DateTimeInterface;

/**
 * Convert a DateTimeInterface to a string using the given format.
 *
 *  ```
 *  use CuyZ\Valinor\NormalizerBuilder;
 *  use CuyZ\Valinor\Normalizer\Configurator\ConvertDateTime;
 *  use CuyZ\Valinor\Normalizer\Format;
 *
 *  $userAsArray = (new NormalizerBuilder())
 *      ->configureWith(new ConvertDateTime(\DateTimeInterface::ATOM))
 *      ->normalizer(Format::array())
 *      ->normalize($user);
 *  // ['createdAt' => '2000-01-01T00:00:00+00:00']
 *  ```
 *
 * @api
 */
final readonly class ConvertDateTime implements NormalizerBuilderConfigurator
{
    /**
     * @param non-empty-string $format
     */
    public function __construct(private string $format) {}

    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder->registerTransformer(
            /** @return non-empty-string */
            fn (DateTimeInterface $date): string => $date->format($this->format)
        );
    }
}
