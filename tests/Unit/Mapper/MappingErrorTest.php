<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class MappingErrorTest extends TestCase
{
    public function test_node_can_be_retrieved(): void
    {
        $value = 'some node value';
        $type = FakeType::thatWillAccept($value);

        $shell = Shell::root($type, []);
        $node = Node::leaf($shell, $value);

        $mappingError = new MappingError($node);

        self::assertSame($node, $mappingError->node());
    }
}
