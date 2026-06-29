<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\NormalizerBuilder;

use function count;
use function current;
use function is_array;

/**
 * Flattens objects that hold a single property, so that instead of
 * `['someProperty' => 'value']` the normalized result is simply `'value'`.
 *
 * This class can be used either as a configurator for global usage or as an
 * attribute to target a specific class or property.
 *
 * Global usage as a configurator
 * ------------------------------
 *
 * When used as a configurator, every object with a single property is
 * flattened.
 *
 * ```
 * use CuyZ\Valinor\Normalizer\Configurator\NormalizeToSingleValue;
 * use CuyZ\Valinor\Normalizer\Format;
 * use CuyZ\Valinor\NormalizerBuilder;
 *
 * final readonly class Email
 * {
 *     public function __construct(
 *         public string $email,
 *     ) {}
 * }
 *
 * $value = (new NormalizerBuilder())
 *     ->configureWith(new NormalizeToSingleValue())
 *     ->normalizer(Format::array())
 *     ->normalize(new Email('john.doe@example.com'));
 *
 * // 'john.doe@example.com'
 * ```
 *
 * Local usage as an attribute
 * ---------------------------
 *
 * When used as an attribute, only the targeted class or property is flattened.
 *
 * ```
 * use CuyZ\Valinor\Normalizer\Configurator\NormalizeToSingleValue;
 * use CuyZ\Valinor\Normalizer\Format;
 * use CuyZ\Valinor\NormalizerBuilder;
 *
 * final readonly class Email
 * {
 *     public function __construct(
 *         public string $email,
 *     ) {}
 * }
 *
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[NormalizeToSingleValue]
 *         public Email $email,
 *     ) {}
 * }
 *
 * $userAsArray = (new NormalizerBuilder())
 *     ->normalizer(Format::array())
 *     ->normalize(new User('John Doe', new Email('john.doe@example.com')));
 *
 * // [
 * //     'name' => 'John Doe',
 * //     'email' => 'john.doe@example.com',
 * // ]
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
#[AsTransformer]
final class NormalizeToSingleValue implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder->registerTransformer($this->normalize(...));
    }

    /**
     * @param callable(): mixed $next
     */
    public function normalize(object $value, callable $next): mixed
    {
        $normalized = $next();

        if (is_array($normalized) && count($normalized) === 1) {
            return current($normalized);
        }

        return $normalized;
    }
}
