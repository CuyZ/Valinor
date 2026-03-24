<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use CuyZ\Valinor\NormalizerBuilder;

use function is_array;
use function lcfirst;
use function preg_replace;
use function strtolower;

/**
 * Convert a DateTimeInterface to a string using the given format.
 *
 *  ```
 *  use CuyZ\Valinor\NormalizerBuilder;
 *  use CuyZ\Valinor\Normalizer\Configurator\ConvertKeysToSnakeCase;
 *  use CuyZ\Valinor\Normalizer\Format;
 *
 *  $userAsArray = (new NormalizerBuilder())
 *      ->configureWith(new ConvertKeysToSnakeCase())
 *      ->normalizer(Format::array())
 *      ->normalize($user);
 *  // ['first_name' => 'John']
 *  ```
 *
 * @api
 */
final readonly class ConvertKeysToSnakeCase implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder->registerTransformer(static function (object $object, callable $next): mixed {
            $result = $next();

            if (! is_array($result)) {
                return $result;
            }

            $snakeCased = [];

            foreach ($result as $key => $value) {
                $lcFirstKey = preg_replace('/[A-Z]/', '_$0', lcfirst($key));
                $newKey = strtolower($lcFirstKey ?? $key);

                $snakeCased[$newKey] = $value;
            }

            return $snakeCased;
        });
    }
}
