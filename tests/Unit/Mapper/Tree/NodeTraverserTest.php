<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\NodeTraverser;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\FakeNode;
use PHPUnit\Framework\TestCase;

final class NodeTraverserTest extends TestCase
{
    public function test_nodes_are_visited(): void
    {
        $children = [
            'foo' => FakeNode::any(),
            'bar' => FakeNode::any(),
        ];

        $node = FakeNode::branch($children);

        $visited = [...(new NodeTraverser(
            fn (Node $node) => $node
        ))->traverse($node)];

        self::assertContains($node, $visited);
        self::assertContains($children['foo'], $visited);
        self::assertContains($children['bar'], $visited);
    }
}
