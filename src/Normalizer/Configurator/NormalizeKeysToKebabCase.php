<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\NormalizerBuilder;

use function is_array;
use function lcfirst;
use function preg_replace;
use function str_replace;
use function strtolower;

/**
 * Normalizes the keys of an object to `kebab-case`.
 *
 * This class can be used either as a configurator for global usage or as an
 * attribute to target a specific class.
 *
 * Global usage as a configurator
 * ------------------------------
 *
 *  ```
 *  use CuyZ\Valinor\NormalizerBuilder;
 *  use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToKebabCase;
 *  use CuyZ\Valinor\Normalizer\Format;
 *
 *  // The keys of every normalized object will be converted to `kebab-case`
 *  $userAsArray = (new NormalizerBuilder())
 *      ->configureWith(new NormalizeKeysToKebabCase())
 *      ->normalizer(Format::array())
 *      ->normalize($user);
 *
 *  // ['first-name' => 'John']
 *  ```
 *
 * Local usage as an attribute
 * ---------------------------
 *
 *  ```
 *  use CuyZ\Valinor\Normalizer\Configurator\NormalizeKeysToKebabCase;
 *  use CuyZ\Valinor\Normalizer\Format;
 *  use CuyZ\Valinor\NormalizerBuilder;
 *
 *  // Only the keys of this class will be converted to `kebab-case`
 *  #[NormalizeKeysToKebabCase]
 *  final readonly class User
 *  {
 *      public function __construct(
 *          public string $firstName,
 *      ) {}
 *  }
 *
 *  $userAsArray = (new NormalizerBuilder())
 *      ->normalizer(Format::array())
 *      ->normalize(new User('John'));
 *
 *  // ['first-name' => 'John']
 *  ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS)]
#[AsTransformer]
final readonly class NormalizeKeysToKebabCase implements NormalizerBuilderConfigurator
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

        $kebabCased = [];

        foreach ($result as $key => $value) {
            $lcFirstKey = preg_replace('/[A-Z]/', '-$0', lcfirst($key));
            $newKey = str_replace('_', '-', strtolower($lcFirstKey ?? $key));

            $kebabCased[$newKey] = $value;
        }

        return $kebabCased;
    }
}
