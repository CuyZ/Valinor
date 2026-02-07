<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\ConverterContainer;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;

final class ConverterContainerTest extends UnitTestCase
{
    public function test_container_checks_converters_only_once(): void
    {
        $functionDefinitionRepository = new FakeFunctionDefinitionRepository();

        $container = new ConverterContainer($functionDefinitionRepository, [
            fn (string $value) => 'foo',
            fn (string $value) => 'bar',
        ]);

        $container->converters();
        $container->converters();

        self::assertSame(2, $functionDefinitionRepository->callCount);
    }
}
