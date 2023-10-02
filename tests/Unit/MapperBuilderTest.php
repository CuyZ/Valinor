<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MapperBuilderTest extends TestCase
{
    private MapperBuilder $mapperBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapperBuilder = new MapperBuilder();
    }

    public function test_builder_methods_return_clone_of_builder_instance(): void
    {
        $builderA = $this->mapperBuilder;
        $builderB = $builderA->infer(DateTimeInterface::class, static fn () => DateTime::class);
        $builderC = $builderA->registerConstructor(static fn (): stdClass => new stdClass());
        $builderD = $builderA->alter(static fn (string $value): string => 'foo');
        $builderE = $builderA->enableFlexibleCasting();
        $builderF = $builderA->allowSuperfluousKeys();
        $builderG = $builderA->allowPermissiveTypes();
        $builderH = $builderA->filterExceptions(fn () => new FakeErrorMessage());
        $builderI = $builderA->withCache(new FakeCache());
        $builderJ = $builderA->supportDateFormats('Y-m-d');

        self::assertNotSame($builderA, $builderB);
        self::assertNotSame($builderA, $builderC);
        self::assertNotSame($builderA, $builderD);
        self::assertNotSame($builderA, $builderE);
        self::assertNotSame($builderA, $builderF);
        self::assertNotSame($builderA, $builderG);
        self::assertNotSame($builderA, $builderH);
        self::assertNotSame($builderA, $builderI);
        self::assertNotSame($builderA, $builderJ);
    }

    public function test_mapper_instance_is_the_same(): void
    {
        self::assertSame($this->mapperBuilder->mapper(), $this->mapperBuilder->mapper());
    }

    public function test_get_supported_date_formats_returns_defaults_formats_when_not_overridden(): void
    {
        self::assertSame(['Y-m-d\\TH:i:sP', 'Y-m-d\\TH:i:s.uP', 'U'], $this->mapperBuilder->supportedDateFormats());
    }

    public function test_get_supported_date_formats_returns_configured_values(): void
    {
        $mapperBuilder = $this->mapperBuilder->supportDateFormats('Y-m-d', 'd/m/Y');

        self::assertSame(['Y-m-d', 'd/m/Y'], $mapperBuilder->supportedDateFormats());
    }

    public function test_get_supported_date_formats_returns_last_values(): void
    {
        $mapperBuilder = $this->mapperBuilder
            ->supportDateFormats('Y-m-d')
            ->supportDateFormats('d/m/Y');

        self::assertSame(['d/m/Y'], $mapperBuilder->supportedDateFormats());
    }

    public function test_supported_date_formats_are_unique(): void
    {
        $mapperBuilder = $this->mapperBuilder->supportDateFormats('Y-m-d', 'd/m/Y', 'Y-m-d');

        self::assertSame(['Y-m-d', 'd/m/Y'], $mapperBuilder->supportedDateFormats());
    }
}
