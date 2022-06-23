<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object\Factory;

use CuyZ\Valinor\Mapper\Object\Factory\CacheObjectBuilderFactory;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fake\Mapper\Object\Factory\FakeObjectBuilderFactory;
use PHPUnit\Framework\TestCase;

final class CacheObjectBuilderFactoryTest extends TestCase
{
    public function test_delegate_result_is_in_cache(): void
    {
        $factory = new CacheObjectBuilderFactory(new FakeObjectBuilderFactory(), new FakeCache());
        $class = FakeClassDefinition::new();

        self::assertSame($factory->for($class), $factory->for($class));
    }
}
