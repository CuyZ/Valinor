<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\MapperBuilder;

use function preg_match;

/**
 * Restricts input keys to `kebab-case` format when mapping data to objects or
 * shaped arrays. If a key does not match, a mapping error will be raised.
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToKebabCase;
 *
 * $user = (new MapperBuilder())
 *     ->configureWith(new RestrictKeysToKebabCase())
 *     ->mapper()
 *     ->map(User::class, [
 *         'first-name' => 'John', // Ok
 *         'last_name' => 'Doe',   // Error
 *     ]);
 * ```
 *
 * @api
 */
final class RestrictKeysToKebabCase implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder->registerKeyConverter(
            // @phpstan-ignore argument.type
            static function (string $key): string {
                if (preg_match('/^[a-z0-9-]*$/', $key) === 0) {
                    throw MessageBuilder::newError('Key must follow the kebab-case format.')->withCode('invalid_key_case')->build();
                }

                return $key;
            }
        );
    }
}
