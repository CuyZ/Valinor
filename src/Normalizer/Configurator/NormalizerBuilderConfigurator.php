<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use CuyZ\Valinor\NormalizerBuilder;

/**
 * Allows applying reusable and shareable configuration logic to a
 * {@see NormalizerBuilder} instance.
 *
 * This is useful when the same normalization configuration needs to be applied
 * in multiple places across an application, or when configuration logic needs
 * to be distributed as a package.
 *
 * ```
 *  use CuyZ\Valinor\NormalizerBuilder;
 *  use CuyZ\Valinor\Normalizer\Configurator\NormalizerBuilderConfigurator;
 *  use CuyZ\Valinor\Normalizer\Format;
 *
 *  final class ApiResponseConfigurator implements NormalizerBuilderConfigurator
 *  {
 *      public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
 *      {
 *          return $builder
 *              ->registerTransformer(
 *                  fn (DateTimeInterface $date) => $date->format('Y-m-d')
 *              )
 *              ->registerTransformer(
 *                  fn (\App\Domain\Money $money) => [
 *                      'amount' => $money->amount,
 *                      'currency' => $money->currency->value,
 *                  ]
 *              );
 *      }
 *  }
 *
 *  // The same configurator can be reused across multiple normalizers
 *  $json = (new NormalizerBuilder())
 *      ->configureWith(new ApiResponseConfigurator())
 *      ->normalizer(Format::json())
 *      ->normalize($someObject);
 *  ```
 *
 * @api
 */
interface NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder;
}
