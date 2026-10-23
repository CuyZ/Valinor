<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Configurator;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\Normalizer\Exception\IgnoreOnNormalizationIsNotRegistered;
use CuyZ\Valinor\NormalizerBuilder;
use JsonSerializable;
use Stringable;

use function array_filter;
use function is_array;

/**
 * Excludes a property from the normalized output, for instance to hide sensitive
 * data such as a password.
 *
 * For the attribute to take effect, an instance of this class **must** also be
 * registered on the normalizer builder via its `configureWith()` method.
 * Without it, the property value is replaced by a placeholder object that
 * raises an exception as soon as it is used (for instance when it is cast to a
 * string or encoded to JSON), pointing to the missing registration.
 *
 * ```
 * use CuyZ\Valinor\Normalizer\Configurator\IgnoreOnNormalization;
 * use CuyZ\Valinor\Normalizer\Format;
 * use CuyZ\Valinor\NormalizerBuilder;
 *
 * final readonly class User
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[IgnoreOnNormalization]
 *         public string $password,
 *     ) {}
 * }
 *
 * // Registering the configurator is required for the attribute to take effect.
 * $userAsArray = (new NormalizerBuilder())
 *     ->configureWith(new IgnoreOnNormalization())
 *     ->normalizer(Format::array())
 *     ->normalize(new User('John Doe', 's3cr3t'));
 *
 * // ['name' => 'John Doe']
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
#[AsTransformer]
final class IgnoreOnNormalization implements NormalizerBuilderConfigurator, Stringable, JsonSerializable
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder->registerTransformer(
            /**
             * @param callable(): mixed $next
             */
            static function (object $value, callable $next): mixed {
                $normalized = $next();

                if (! is_array($normalized)) {
                    return $normalized;
                }

                return array_filter(
                    $normalized,
                    static fn (mixed $item): bool => ! $item instanceof self,
                );
            },
        );
    }

    public function normalize(mixed $value): self
    {
        return $this;
    }

    public function __toString(): never
    {
        throw new IgnoreOnNormalizationIsNotRegistered();
    }

    public function jsonSerialize(): never
    {
        throw new IgnoreOnNormalizationIsNotRegistered();
    }
}
