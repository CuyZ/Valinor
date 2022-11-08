<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\CallbackMessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use PHPUnit\Framework\TestCase;

final class CallbackMessageFormatterTest extends TestCase
{
    public function test_callbacks_is_called_and_modifies_message(): void
    {
        $message = (FakeNodeMessage::withBody('some message with {some_parameter}'))
            ->withParameter('some_parameter', 'some_value');

        $formatter = new CallbackMessageFormatter(
            fn (NodeMessage $message) => $message->withBody('some new message with {some_parameter}')
        );

        self::assertSame('some new message with some_value', $formatter->format($message)->toString());
    }
}
