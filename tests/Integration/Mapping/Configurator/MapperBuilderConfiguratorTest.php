<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function strtoupper;

final class MapperBuilderConfiguratorTest extends IntegrationTestCase
{
    public function test_configure_with_applies_configurator(): void
    {
        $configurator = new class () implements MapperBuilderConfigurator {
            public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
            {
                return $builder->registerConverter(
                    fn (string $value): string => strtoupper($value)
                );
            }
        };

        $result = $this->mapperBuilder()
            ->configureWith($configurator)
            ->mapper()
            ->map('string', 'foo');

        self::assertSame('FOO', $result);
    }

    public function test_configure_with_applies_multiple_configurators(): void
    {
        $configuratorA = new class () implements MapperBuilderConfigurator {
            public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
            {
                return $builder->registerConverter(
                    /**
                     * @template T
                     * @param callable(): T $next
                     */
                    fn (string $value, callable $next): string => $next() . '!' // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                );
            }
        };

        $configuratorB = new class () implements MapperBuilderConfigurator {
            public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
            {
                return $builder->registerConverter(
                    /**
                     * @template T
                     * @param callable(): T $next
                     */
                    fn (string $value, callable $next): string => $next() . '?' // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                );
            }
        };

        $result = $this->mapperBuilder()
            ->configureWith($configuratorA, $configuratorB)
            ->mapper()
            ->map('string', 'foo');

        self::assertSame('foo?!', $result);
    }
}
