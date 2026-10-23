<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use CuyZ\Valinor\MapperBuilder;

use function lcfirst;
use function str_replace;
use function ucwords;

/**
 * @deprecated use {@see MapKeysToCamelCase} instead.
 *
 * @api
 */
final class ConvertKeysToCamelCase implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder->registerKeyConverter(
            static fn (string $key): string => lcfirst(str_replace(['_', '-'], '', ucwords($key, '_-'))), // @phpstan-ignore argument.type
        );
    }
}
