<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeNode;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use PHPUnit\Framework\TestCase;

final class MessageMapFormatterTest extends TestCase
{
    public function test_format_finds_code_returns_formatted_content(): void
    {
        $message = new NodeMessage(FakeNode::any(), new FakeMessage());
        $formatter = new MessageMapFormatter([
            'some_code' => 'foo',
        ]);

        self::assertSame('foo', $formatter->format($message));
    }

    public function test_format_finds_code_returns_formatted_content_from_callback(): void
    {
        $message = new NodeMessage(FakeNode::any(), new FakeMessage());
        $formatter = new MessageMapFormatter([
            'some_code' => fn (NodeMessage $message) => "foo $message",
        ]);

        self::assertSame('foo some message', $formatter->format($message));
    }

    public function test_format_finds_content_returns_formatted_content(): void
    {
        $message = new NodeMessage(FakeNode::any(), new FakeMessage());
        $formatter = new MessageMapFormatter([
            'some message' => 'foo',
        ]);

        self::assertSame('foo', $formatter->format($message));
    }

    public function test_format_finds_class_name_returns_formatted_content(): void
    {
        $message = new NodeMessage(FakeNode::any(), new FakeMessage());
        $formatter = new MessageMapFormatter([
            FakeMessage::class => 'foo',
        ]);

        self::assertSame('foo', $formatter->format($message));
    }

    public function test_format_does_not_find_any_returns_default(): void
    {
        $message = new NodeMessage(FakeNode::any(), new FakeMessage());
        $formatter = (new MessageMapFormatter([]))->defaultsTo('foo');

        self::assertSame('foo', $formatter->format($message));
    }

    public function test_format_does_not_find_any_returns_message_content(): void
    {
        $message = new NodeMessage(FakeNode::any(), new FakeMessage());
        $formatter = new MessageMapFormatter([]);

        self::assertSame('some message', $formatter->format($message));
    }
}
