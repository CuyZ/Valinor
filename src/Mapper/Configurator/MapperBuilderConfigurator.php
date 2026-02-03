<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use CuyZ\Valinor\MapperBuilder;

/**
 * Allows applying reusable and shareable configuration logic to a
 * {@see MapperBuilder} instance.
 *
 * This is useful when the same mapping configuration needs to be applied in
 * multiple places across an application, or when configuration logic needs to
 * be distributed as a package.
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;
 *
 * final class ApplicationMappingConfigurator implements MapperBuilderConfigurator
 * {
 *     public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
 *     {
 *         return $builder
 *             ->allowSuperfluousKeys()
 *             ->registerConstructor(
 *                 \App\Domain\CustomerId::fromString(...),
 *             );
 *     }
 * }
 *
 * // The same configurator can be reused across multiple mappers
 * $result = (new MapperBuilder())
 *     ->configureWith(new ApplicationMappingConfigurator())
 *     ->mapper()
 *     ->map(SomeClass::class, [
 *          // …
 *      ]);
 * ```
 *
 * @api
 */
interface MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder;
}
