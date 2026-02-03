<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\NormalizerBuilderConfigurator;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function strtoupper;

final class NormalizerBuilderConfiguratorTest extends IntegrationTestCase
{
    public function test_configure_with_applies_configurator(): void
    {
        $configurator = new class () implements NormalizerBuilderConfigurator {
            public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
            {
                return $builder->registerTransformer(
                    fn (string $value) => strtoupper($value)
                );
            }
        };

        $result = $this->normalizerBuilder()
            ->configureWith($configurator)
            ->normalizer(Format::array())
            ->normalize('foo');

        self::assertSame('FOO', $result);
    }

    public function test_configure_with_applies_multiple_configurators(): void
    {
        $configuratorA = new class () implements NormalizerBuilderConfigurator {
            public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
            {
                return $builder->registerTransformer(
                    fn (string $value, callable $next) => $next() . '!' // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                );
            }
        };

        $configuratorB = new class () implements NormalizerBuilderConfigurator {
            public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
            {
                return $builder->registerTransformer(
                    fn (string $value, callable $next) => $next() . '?' // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                );
            }
        };

        $result = $this->normalizerBuilder()
            ->configureWith($configuratorA, $configuratorB)
            ->normalizer(Format::array())
            ->normalize('foo');

        self::assertSame('foo?!', $result);
    }
}
