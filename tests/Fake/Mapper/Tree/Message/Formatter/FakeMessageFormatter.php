<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

final class FakeMessageFormatter implements MessageFormatter
{
    private string $prefix;

    private string $body;

    public static function withPrefix(string $prefix = 'formatted:'): self
    {
        $instance = new self();
        $instance->prefix = $prefix;

        return $instance;
    }

    public static function withBody(string $body): self
    {
        $instance = new self();
        $instance->body = $body;

        return $instance;
    }

    public function format(NodeMessage $message): NodeMessage
    {
        if (isset($this->prefix)) {
            $body = "$this->prefix {$message->body()}";
        } elseif (isset($this->body)) {
            $body = $this->body;
        } else {
            $body = $message->body();
        }

        return $message->withBody($body);
    }
}
