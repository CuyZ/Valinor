<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder\FakeNodeBuilder;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use PHPUnit\Framework\TestCase;

final class RootNodeBuilderTest extends TestCase
{
    public function test_with_type_as_current_root_returns_clone(): void
    {
        $rootNodeBuilderA = new RootNodeBuilder(new FakeNodeBuilder());
        $rootNodeBuilderB = $rootNodeBuilderA->withTypeAsCurrentRoot(new FakeObjectType());

        self::assertNotSame($rootNodeBuilderA, $rootNodeBuilderB);
    }
}
