<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache;

use CuyZ\Valinor\Definition\Repository\Cache\InMemoryClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use PHPUnit\Framework\TestCase;

final class InMemoryClassDefinitionRepositoryTest extends TestCase
{
    public function test_delegate_result_is_cached_in_memory(): void
    {
        $type = new FakeObjectType();

        $delegate = $this->createMock(ClassDefinitionRepository::class);
        $factory = new InMemoryClassDefinitionRepository($delegate);

        $delegate
            ->expects(self::once())
            ->method('for')
            ->willReturn(FakeClassDefinition::new());

        $factory->for($type);
        $factory->for($type);
    }
}
