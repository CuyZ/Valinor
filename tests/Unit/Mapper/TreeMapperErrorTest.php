<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper;

use CuyZ\Valinor\Mapper\TypeTreeMapperError;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\FakeNode;
use PHPUnit\Framework\TestCase;

final class TreeMapperErrorTest extends TestCase
{
    public function test_node_can_be_retrieved(): void
    {
        $node = FakeNode::any();

        $mappingError = new TypeTreeMapperError($node);

        self::assertSame($node, $mappingError->node());
    }
}
