<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

final class FakeMessageFormatter implements MessageFormatter
{
    private string $body;

    public function __construct(string $body = null)
    {
        if ($body) {
            $this->body = $body;
        }
    }

    public function format(NodeMessage $message): NodeMessage
    {
        return $message->withBody($this->body ?? "formatted: {$message->body()}");
    }
}
