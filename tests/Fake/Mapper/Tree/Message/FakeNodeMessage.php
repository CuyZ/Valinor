<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\FakeNode;

final class FakeNodeMessage
{
    public static function any(): NodeMessage
    {
        return self::build(new FakeMessage());
    }

    public static function withMessage(Message $message): NodeMessage
    {
        return self::build($message);
    }

    public static function withBody(string $body): NodeMessage
    {
        return self::build(new FakeMessage($body));
    }

    private static function build(Message $message): NodeMessage
    {
        return new NodeMessage(
            FakeNode::any(),
            $message
        );
    }
}
