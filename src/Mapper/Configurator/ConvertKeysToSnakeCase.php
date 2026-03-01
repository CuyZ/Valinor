<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use CuyZ\Valinor\MapperBuilder;

use function ltrim;
use function preg_replace;
use function str_replace;
use function strtolower;

/**
 * Converts the keys of input data to `snake_case` before mapping them to
 * object properties or shaped array keys. This allows accepting data with a
 * different naming convention than the one used in the PHP codebase.
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToSnakeCase;
 *
 * $user = (new MapperBuilder())
 *     ->configureWith(new ConvertKeysToSnakeCase())
 *     ->mapper()
 *     ->map(User::class, [
 *         'firstName' => 'John', // mapped to `$first_name`
 *         'lastName' => 'Doe',   // mapped to `$last_name`
 *     ]);
 * ```
 *
 * This configurator can be combined with a key restriction configurator to both
 * validate and convert keys in a single step. The restriction configurator must
 * be registered *before* the conversion so that the validation runs on the
 * original input keys.
 *
 * - {@see RestrictKeysToCamelCase}
 * - {@see RestrictKeysToKebabCase}
 * - {@see RestrictKeysToPascalCase}
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToSnakeCase;
 * use CuyZ\Valinor\Mapper\Configurator\RestrictKeysToCamelCase;
 *
 * $user = (new MapperBuilder())
 *     ->configureWith(
 *         new RestrictKeysToCamelCase(),
 *         new ConvertKeysToSnakeCase(),
 *     )
 *     ->mapper()
 *     ->map(User::class, [
 *         'firstName' => 'John',
 *         'lastName' => 'Doe',
 *     ]);
 * ```
 *
 * @api
 */
final class ConvertKeysToSnakeCase implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder->registerKeyConverter(
            static fn (string $key): string => ltrim(strtolower((string)preg_replace('/[A-Z]/', '_$0', str_replace('-', '_', $key))), '_') // @phpstan-ignore argument.type
        );
    }
}
