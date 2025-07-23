<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object\Factory;

use CuyZ\Valinor\Mapper\Object\Factory\InMemoryObjectBuilderFactory;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fake\Mapper\Object\Factory\FakeObjectBuilderFactory;
use PHPUnit\Framework\TestCase;

final class InMemoryObjectBuilderFactoryTest extends TestCase
{
    public function test_delegate_result_is_cached_in_memory(): void
    {
        $factory = new InMemoryObjectBuilderFactory(new FakeObjectBuilderFactory());
        $class = FakeClassDefinition::new();

        self::assertSame($factory->for($class), $factory->for($class));
    }
}
