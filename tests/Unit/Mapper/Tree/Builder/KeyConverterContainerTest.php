<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\KeyConversionPipeline;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use Throwable;

final class KeyConverterContainerTest extends UnitTestCase
{
    public function test_container_checks_converter_only_once(): void
    {
        $functionDefinitionRepository = new FakeFunctionDefinitionRepository();

        $container = new KeyConversionPipeline(
            $functionDefinitionRepository,
            [
                fn (string $key): string => $key,
                fn (string $key): string => $key,
            ],
            static fn (Throwable $error) => throw $error,
        );

        $container->convert([]);
        $container->convert([]);

        self::assertSame(2, $functionDefinitionRepository->callCount);
    }
}
