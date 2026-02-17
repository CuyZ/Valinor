<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\MapperBuilder;

use function preg_match;

/**
 * Restricts input keys to `PascalCase` format when mapping data to objects or
 * shaped arrays. If a key does not match, a mapping error will be raised.
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToPascalCase;
 *
 * $user = (new MapperBuilder())
 *     ->configureWith(new RestrictKeysToPascalCase())
 *     ->mapper()
 *     ->map(User::class, [
 *         'FirstName' => 'John', // Ok
 *         'last_name' => 'Doe',  // Error
 *     ]);
 * ```
 *
 * @api
 */
final class RestrictKeysToPascalCase implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder->registerKeyConverter(
            // @phpstan-ignore argument.type
            static function (string $key): string {
                if (preg_match('/^[A-Z][a-zA-Z0-9]*$/', $key) === 0) {
                    throw MessageBuilder::newError('Key must follow the PascalCase format.')->withCode('invalid_key_case')->build();
                }

                return $key;
            }
        );
    }
}
