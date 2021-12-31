<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\NodeTraverser;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeNode;
use PHPUnit\Framework\TestCase;

final class NodeTraverserTest extends TestCase
{
    public function test_nodes_are_visited(): void
    {
        $node = FakeNode::branch([
            'foo' => [],
            'bar' => [],
        ]);

        $visited = [...(new NodeTraverser(
            fn (Node $node) => $node
        ))->traverse($node)];

        self::assertContains($node, $visited);
        self::assertContains($node->children()['foo'], $visited);
        self::assertContains($node->children()['bar'], $visited);
    }
}
