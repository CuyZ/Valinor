<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit;

use CuyZ\Valinor\MapperBuilder;
use DateTime;
use DateTimeInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

use function sys_get_temp_dir;

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
        $builderC = $builderA->bind(static fn (): stdClass => new stdClass());
        $builderD = $builderA->alter(static fn (string $value): string => 'foo');
        $builderE = $builderA->withCacheDir(sys_get_temp_dir());
        $builderF = $builderA->enableLegacyDoctrineAnnotations();

        self::assertNotSame($builderA, $builderB);
        self::assertNotSame($builderA, $builderC);
        self::assertNotSame($builderA, $builderD);
        self::assertNotSame($builderA, $builderE);
        self::assertNotSame($builderA, $builderF);
    }

    public function test_mapper_instance_is_the_same(): void
    {
        self::assertSame($this->mapperBuilder->mapper(), $this->mapperBuilder->mapper());
    }

    public function test_bind_with_callable_with_no_return_type_throws_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No return type was found for this callable.');

        // @phpstan-ignore-next-line
        $this->mapperBuilder->bind(static function () {
        });
    }

    public function test_alter_with_callable_with_no_parameter_throws_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('One parameter is required for this callable.');

        $this->mapperBuilder->alter(static function (): void {
        });
    }

    public function test_alter_with_callable_with_parameter_with_no_type_throws_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No type was found for the parameter of this callable.');

        $this->mapperBuilder->alter(static function ($foo): void {
        });
    }
}
