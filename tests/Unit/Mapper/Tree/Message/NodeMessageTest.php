<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeTranslatableMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\Formatter\FakeMessageFormatter;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class NodeMessageTest extends TestCase
{
    public function test_node_properties_can_be_accessed(): void
    {
        $originalMessage = new FakeMessage();
        $type = FakeType::permissive();
        $attributes = new FakeAttributes();

        $shell = FakeShell::any()->child('foo', $type, $attributes)->withValue('some value');
        $message = new NodeMessage($shell, $originalMessage);

        self::assertSame('foo', $message->name());
        self::assertSame('foo', $message->path());
        self::assertSame('some value', $message->value());
        self::assertSame($type, $message->type());
        self::assertSame($attributes, $message->attributes());
        self::assertSame($originalMessage, $message->originalMessage());
    }

    public function test_message_is_error_if_original_message_is_throwable(): void
    {
        $originalMessage = new FakeErrorMessage();
        $message = new NodeMessage(FakeShell::any(), $originalMessage);

        self::assertTrue($message->isError());
        self::assertSame('1652883436', $message->code());
        self::assertSame('some error message', $message->body());
    }

    public function test_parameters_are_replaced_in_body(): void
    {
        $originalMessage = new FakeTranslatableMessage('some original message', ['some_parameter' => 'some parameter value']);
        $type = FakeType::permissive();
        $shell = FakeShell::any()->child('foo', $type)->withValue('some value');

        $message = new NodeMessage($shell, $originalMessage);
        $message = $message->withBody('{message_code} / {node_name} / {node_path} / {node_type} / {original_value} / {original_message} / {some_parameter}');

        self::assertSame("1652902453 / foo / foo / `$type` / 'some value' / some original message (toString) / some parameter value", (string)$message);
    }

    public function test_replaces_correct_original_message_if_throwable(): void
    {
        $message = new NodeMessage(FakeShell::any(), new FakeErrorMessage('some error message'));
        $message = $message->withBody('original: {original_message}');

        self::assertSame('original: some error message', (string)$message);
    }

    public function test_format_message_uses_formatter_to_replace_content(): void
    {
        $originalMessage = new FakeMessage('some message');

        $message = new NodeMessage(FakeShell::any(), $originalMessage);
        $formattedMessage = (new FakeMessageFormatter())->format($message);

        self::assertNotSame($message, $formattedMessage);
        self::assertSame('formatted: some message', (string)$formattedMessage);
    }

    public function test_custom_body_returns_clone(): void
    {
        $messageA = FakeNodeMessage::any();
        $messageB = $messageA->withBody('some other message');

        self::assertNotSame($messageA, $messageB);
    }

    public function test_custom_locale_returns_clone(): void
    {
        $messageA = FakeNodeMessage::any();
        $messageB = $messageA->withLocale('fr');

        self::assertNotSame($messageA, $messageB);
    }

    public function test_custom_locale_is_used(): void
    {
        $originalMessage = new FakeTranslatableMessage('un message: {value, spellout}', ['value' => '42']);

        $message = new NodeMessage(FakeShell::any(), $originalMessage);
        $message = $message->withLocale('fr');

        self::assertSame('un message: quarante-deux', (string)$message);
    }

    public function test_message_with_no_code_returns_unknown(): void
    {
        $originalMessage = new class () implements Message {
            public function __toString(): string
            {
                return 'some message';
            }
        };

        $message = new NodeMessage(FakeShell::any(), $originalMessage);

        self::assertSame('unknown', $message->code());
    }
}
