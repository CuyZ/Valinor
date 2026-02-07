<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;

final class MessageMapFormatterTest extends UnitTestCase
{
    public function test_format_finds_code_returns_formatted_content(): void
    {
        $formatter = (new MessageMapFormatter([
            'some_code' => 'ok',
            'some message' => 'nope',
            FakeMessage::class => 'nope',
        ]))->defaultsTo('nope');

        $message = $formatter->format(FakeNodeMessage::new());

        self::assertSame('ok', (string)$message);
    }

    public function test_format_finds_code_returns_formatted_content_from_callback(): void
    {
        $formatter = (new MessageMapFormatter([
            'some_code' => fn (NodeMessage $message) => "ok $message",
            'some message' => 'nope',
            FakeMessage::class => 'nope',
        ]))->defaultsTo('nope');

        $message = $formatter->format(FakeNodeMessage::new());

        self::assertSame('ok some message', (string)$message);
    }

    public function test_format_finds_body_returns_formatted_content(): void
    {
        $formatter = (new MessageMapFormatter([
            'some message' => 'ok',
            FakeMessage::class => 'nope',
        ]))->defaultsTo('nope');

        $message = $formatter->format(FakeNodeMessage::new());

        self::assertSame('ok', (string)$message);
    }

    public function test_format_finds_class_name_returns_formatted_content(): void
    {
        $formatter = (new MessageMapFormatter([
            FakeMessage::class => 'foo',
        ]))->defaultsTo('nope');

        $message = $formatter->format(FakeNodeMessage::new());

        self::assertSame('foo', (string)$message);
    }

    public function test_format_does_not_find_any_returns_default(): void
    {
        $formatter = (new MessageMapFormatter([]))->defaultsTo('foo');

        $message = $formatter->format(FakeNodeMessage::new());

        self::assertSame('foo', (string)$message);
    }

    public function test_format_does_not_find_any_returns_message_content(): void
    {
        $formatter = new MessageMapFormatter([]);

        $message = $formatter->format(FakeNodeMessage::new());

        self::assertSame('some message', (string)$message);
    }

    public function test_default_to_returns_another_instance(): void
    {
        $formatterA = new MessageMapFormatter([]);
        $formatterB = $formatterA->defaultsTo('foo');

        self::assertNotSame($formatterA, $formatterB);
    }
}
