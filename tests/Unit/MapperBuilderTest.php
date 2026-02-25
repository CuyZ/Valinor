<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use DateTime;
use DateTimeInterface;
use stdClass;

use function strtoupper;

final class MapperBuilderTest extends UnitTestCase
{
    public function test_builder_methods_return_clone_of_builder_instance(): void
    {
        $mapperBuilder = new MapperBuilder();

        $builders = [
            $mapperBuilder->infer(DateTimeInterface::class, static fn () => DateTime::class),
            $mapperBuilder->registerConstructor(static fn (): stdClass => new stdClass()),
            $mapperBuilder->allowScalarValueCasting(),
            $mapperBuilder->allowNonSequentialList(),
            $mapperBuilder->allowSuperfluousKeys(),
            $mapperBuilder->allowPermissiveTypes(),
            $mapperBuilder->registerConverter(fn (string $value) => $value),
            $mapperBuilder->registerKeyConverter(fn (string $value) => $value),
            $mapperBuilder->filterExceptions(fn () => new FakeErrorMessage()),
            $mapperBuilder->withCache(new FakeCache()),
            $mapperBuilder->supportDateFormats('Y-m-d'),
        ];

        foreach ($builders as $builder) {
            self::assertNotSame($mapperBuilder, $builder);
        }
    }

    public function test_mapper_instance_is_the_same(): void
    {
        $mapperBuilder = new MapperBuilder();

        self::assertSame($mapperBuilder->mapper(), $mapperBuilder->mapper());
    }

    public function test_get_supported_date_formats_returns_defaults_formats_when_not_overridden(): void
    {
        $mapperBuilder = new MapperBuilder();

        self::assertSame(['Y-m-d\\TH:i:sP', 'Y-m-d\\TH:i:s.uP', 'U', 'U.u'], $mapperBuilder->supportedDateFormats());
    }

    public function test_get_supported_date_formats_returns_configured_values(): void
    {
        $mapperBuilder = (new MapperBuilder())->supportDateFormats('Y-m-d', 'd/m/Y');

        self::assertSame(['Y-m-d', 'd/m/Y'], $mapperBuilder->supportedDateFormats());
    }

    public function test_get_supported_date_formats_returns_last_values(): void
    {
        $mapperBuilder = (new MapperBuilder())
            ->supportDateFormats('Y-m-d')
            ->supportDateFormats('d/m/Y');

        self::assertSame(['d/m/Y'], $mapperBuilder->supportedDateFormats());
    }

    public function test_supported_date_formats_are_unique(): void
    {
        $mapperBuilder = (new MapperBuilder())->supportDateFormats('Y-m-d', 'd/m/Y', 'Y-m-d');

        self::assertSame(['Y-m-d', 'd/m/Y'], $mapperBuilder->supportedDateFormats());
    }

    public function test_settings_are_cloned_when_configuring_mapper_builder(): void
    {
        $mapperBuilder = new MapperBuilder();

        $resultA = $mapperBuilder
            ->registerConverter(fn (string $value): string => strtoupper($value))
            ->mapper()
            ->map('string', 'foo');

        $resultB = $mapperBuilder
            ->registerConverter(fn (string $value): string => $value . '!')
            ->mapper()
            ->map('string', 'foo');

        self::assertSame('FOO', $resultA);
        self::assertSame('foo!', $resultB);
    }
}
