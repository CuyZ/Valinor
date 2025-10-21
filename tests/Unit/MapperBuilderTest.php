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

use function strtoupper;

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
        $builders = [
            $this->mapperBuilder->infer(DateTimeInterface::class, static fn () => DateTime::class),
            $this->mapperBuilder->registerConstructor(static fn (): stdClass => new stdClass()),
            $this->mapperBuilder->allowScalarValueCasting(),
            $this->mapperBuilder->allowNonSequentialList(),
            $this->mapperBuilder->allowSuperfluousKeys(),
            $this->mapperBuilder->allowPermissiveTypes(),
            $this->mapperBuilder->registerConverter(fn (string $value) => $value),
            $this->mapperBuilder->filterExceptions(fn () => new FakeErrorMessage()),
            $this->mapperBuilder->withCache(new FakeCache()),
            $this->mapperBuilder->supportDateFormats('Y-m-d'),
        ];

        foreach ($builders as $builder) {
            self::assertNotSame($this->mapperBuilder, $builder);
        }
    }

    public function test_mapper_instance_is_the_same(): void
    {
        self::assertSame($this->mapperBuilder->mapper(), $this->mapperBuilder->mapper());
    }

    public function test_get_supported_date_formats_returns_defaults_formats_when_not_overridden(): void
    {
        self::assertSame(['Y-m-d\\TH:i:sP', 'Y-m-d\\TH:i:s.uP', 'U', 'U.u'], $this->mapperBuilder->supportedDateFormats());
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

    public function test_settings_are_cloned_when_configuring_mapper_builder(): void
    {
        $resultA = $this->mapperBuilder
            ->registerConverter(fn (string $value): string => strtoupper($value))
            ->mapper()
            ->map('string', 'foo');

        $resultB = $this->mapperBuilder
            ->registerConverter(fn (string $value): string => $value . '!')
            ->mapper()
            ->map('string', 'foo');

        self::assertSame('FOO', $resultA);
        self::assertSame('foo!', $resultB);
    }
}
