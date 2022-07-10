<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Node;

final class FakeNode
{
    public static function any(): Node
    {
        return self::build([], []);
    }

    /**
     * @param array<Node> $children
     */
    public static function branch(array $children): Node
    {
        return self::build([], $children);
    }

    public static function withMessage(Message $message): Node
    {
        return self::build([$message], []);
    }

    /**
     * @param array<Message> $messages
     * @param array<Node> $children
     */
    private static function build(array $messages, array $children): Node
    {
        return new Node(
            true,
            'nodeName',
            'some.node.path',
            'string',
            true,
            'some source value',
            'some value',
            $messages,
            $children,
        );
    }
}
