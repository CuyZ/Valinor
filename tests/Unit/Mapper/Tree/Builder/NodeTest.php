<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\Node;
use PHPUnit\Framework\TestCase;

final class NodeTest extends TestCase
{
    public function test_new_node_has_correct_values(): void
    {
        $node = Node::new('foo', 2);

        self::assertTrue($node->isValid());
        self::assertSame('foo', $node->value());
        self::assertSame([], $node->messages());
        self::assertSame(2, $node->childrenCount());
    }

    public function test_new_node_with_no_children_count_has_0_children_count(): void
    {
        $node = Node::new('foo');

        self::assertSame(0, $node->childrenCount());
    }

    public function test_branch_error_node_has_0_child(): void
    {
        $node = Node::branchWithErrors(['foo' => Node::new('foo')]);

        self::assertSame(0, $node->childrenCount());
    }
}
