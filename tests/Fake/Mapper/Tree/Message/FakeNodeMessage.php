<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Mapper\Tree\Node;

final class FakeNodeMessage
{
    public static function new(
        ?Message $message = null,
        ?string $body = null,
        ?string $name = null,
        ?string $path = null,
        ?string $type = null,
        ?string $sourceValue = null,
    ): NodeMessage {
        return new NodeMessage(
            node: new Node(
                isRoot: true,
                name: $name ?? 'some_name',
                path: $path ?? 'some.path',
                type: $type ?? 'mixed',
                sourceFilled: true,
                sourceValue: $sourceValue ?? 'some value',
                mappedValue: 'some value',
                messages: [],
                children: [],
            ),
            message: $message ?? new FakeMessage($body ?? 'some message'),
        );
    }
}
