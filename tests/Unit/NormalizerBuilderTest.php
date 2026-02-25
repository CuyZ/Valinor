<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit;

use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use stdClass;

use function strtoupper;

final class NormalizerBuilderTest extends UnitTestCase
{
    public function test_builder_methods_return_clone_of_builder_instance(): void
    {
        $builderA = new NormalizerBuilder();
        $builderB = $builderA->withCache(new FakeCache());
        $builderC = $builderA->registerTransformer(fn (stdClass $object) => 'foo');

        self::assertNotSame($builderA, $builderB);
        self::assertNotSame($builderA, $builderC);
    }

    public function test_normalizer_instance_is_the_same(): void
    {
        $normalizerBuilder = new NormalizerBuilder();

        self::assertSame(
            $normalizerBuilder->normalizer(Format::array()),
            $normalizerBuilder->normalizer(Format::array()),
        );
    }

    public function test_settings_are_cloned_when_configuring_normalizer_builder(): void
    {
        $normalizerBuilder = new NormalizerBuilder();

        $resultA = $normalizerBuilder
            ->registerTransformer(fn (string $value) => strtoupper($value))
            ->normalizer(Format::array())
            ->normalize('foo');

        $resultB = $normalizerBuilder
            ->registerTransformer(fn (string $value) => $value . '!')
            ->normalizer(Format::array())
            ->normalize('foo');

        self::assertSame('FOO', $resultA);
        self::assertSame('foo!', $resultB);
    }
}
