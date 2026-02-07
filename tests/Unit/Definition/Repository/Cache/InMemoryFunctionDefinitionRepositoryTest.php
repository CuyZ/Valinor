<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\Repository\Cache\InMemoryFunctionDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;

final class InMemoryFunctionDefinitionRepositoryTest extends UnitTestCase
{
    public function test_delegate_result_is_cached_in_memory(): void
    {
        $callable = fn () => 'foo';

        $delegate = $this->createMock(FunctionDefinitionRepository::class);
        $factory = new InMemoryFunctionDefinitionRepository($delegate);

        $delegate
            ->expects(self::once())
            ->method('for')
            ->willReturn(FakeFunctionDefinition::new());

        $factory->for($callable);
        $factory->for($callable);
    }
}
