<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use CuyZ\Valinor\MapperBuilder;

use function ltrim;
use function preg_replace;
use function str_replace;
use function strtolower;

/**
 * @deprecated use {@see MapKeysToSnakeCase} instead.
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
