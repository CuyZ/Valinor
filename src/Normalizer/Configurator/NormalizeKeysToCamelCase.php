<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\NormalizerBuilder;

use function is_array;
use function lcfirst;
use function str_replace;
use function ucwords;

/**
 * Normalizes the keys of an object to `camelCase`.
 *
 * This class can be used either as a configurator for global usage or as an
 * attribute to target a specific class.
 *
 * Global usage as a configurator
 * ------------------------------
 *
 *  ```
 *  use CuyZ\Valinor\NormalizerBuilder;
 *  use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToCamelCase;
 *  use CuyZ\Valinor\Normalizer\Format;
 *
 *  // The keys of every normalized object will be converted to `camelCase`
 *  $userAsArray = (new NormalizerBuilder())
 *      ->configureWith(new NormalizeKeysToCamelCase())
 *      ->normalizer(Format::array())
 *      ->normalize($user);
 *
 *  // ['firstName' => 'John']
 *  ```
 *
 * Local usage as an attribute
 * ---------------------------
 *
 *  ```
 *  use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToCamelCase;
 *  use CuyZ\Valinor\Normalizer\Format;
 *  use CuyZ\Valinor\NormalizerBuilder;
 *
 *  // Only the keys of this class will be converted to `camelCase`
 *  #[NormalizeKeysToCamelCase]
 *  final readonly class User
 *  {
 *      public function __construct(
 *          public string $first_name,
 *      ) {}
 *  }
 *
 *  $userAsArray = (new NormalizerBuilder())
 *      ->normalizer(Format::array())
 *      ->normalize(new User('John'));
 *
 *  // ['firstName' => 'John']
 *  ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS)]
#[AsTransformer]
final readonly class NormalizeKeysToCamelCase implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder->registerTransformer($this->normalize(...));
    }

    public function normalize(object $object, callable $next): mixed
    {
        $result = $next();

        if (! is_array($result)) {
            return $result;
        }

        $camelCased = [];

        foreach ($result as $key => $value) {
            $newKey = lcfirst(str_replace(['_', '-'], '', ucwords($key, '_-')));

            $camelCased[$newKey] = $value;
        }

        return $camelCased;
    }
}
