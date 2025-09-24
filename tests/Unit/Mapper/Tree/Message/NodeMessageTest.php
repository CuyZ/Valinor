<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

final class NodeMessageTest extends TestCase
{
    public function test_node_properties_can_be_accessed(): void
    {
        $originalMessage = new FakeMessage();

        $message = new NodeMessage(
            $originalMessage,
            body: 'some message',
            name: 'nodeName',
            path: 'some.node.path',
            type: 'string',
            expectedSignature: 'expected_signature',
            sourceValue: 'some source value',
        );

        self::assertSame($originalMessage, $message->originalMessage());
        self::assertSame('nodeName', $message->name());
        self::assertSame('some.node.path', $message->path());
        self::assertSame('string', $message->type());
        self::assertSame('expected_signature', $message->expectedSignature());
        self::assertSame('some source value', $message->sourceValue());
        self::assertFalse($message->isError());
    }

    public function test_message_is_error_if_original_message_is_throwable(): void
    {
        $originalMessage = new FakeErrorMessage();
        $message = FakeNodeMessage::new(message: $originalMessage);

        self::assertTrue($message->isError());
        self::assertSame('1652883436', $message->code());
    }

    public function test_parameters_are_replaced_in_body(): void
    {
        $message = FakeNodeMessage::new(
            message: (new FakeMessage('some original message'))
                ->withParameters(['some_parameter' => 'some parameter value']),
            body: '{message_code} / {node_name} / {node_path} / {node_type} / {source_value} / {original_message} / {some_parameter}',
            name: 'nodeName',
            path: 'some.path',
            type: '`string`',
            sourceValue: "'some source value'",
        );

        $expected = "some_code / nodeName / some.path / `string` / 'some source value' / some original message / some parameter value";

        self::assertSame($expected, $message->toString());
        self::assertSame($expected, (string)$message);
    }

    public function test_custom_parameters_are_replaced_in_body(): void
    {
        $message = FakeNodeMessage::new(body: 'before / {some_parameter} / after');
        $message = $message->withParameter('some_parameter', 'some value');

        $expected = 'before / some value / after';

        self::assertSame($expected, $message->toString());
        self::assertSame($expected, (string)$message);
    }

    public function test_replaces_correct_original_message_if_throwable(): void
    {
        $originalMessage = new FakeErrorMessage('some error message');

        $message = FakeNodeMessage::new(message: $originalMessage);
        $message = $message->withBody('original: {original_message}');

        $expected = 'original: some error message';

        self::assertSame($expected, $message->toString());
        self::assertSame($expected, (string)$message);
    }

    public function test_custom_body_returns_clone(): void
    {
        $messageA = FakeNodeMessage::new();
        $messageB = $messageA->withBody('some other message');

        self::assertNotSame($messageA, $messageB);
    }

    public function test_add_parameter_returns_clone(): void
    {
        $messageA = FakeNodeMessage::new();
        $messageB = $messageA->withParameter('some_parameter', 'some value');

        self::assertNotSame($messageA, $messageB);
    }

    public function test_custom_locale_returns_clone(): void
    {
        $messageA = FakeNodeMessage::new();
        $messageB = $messageA->withLocale('fr');

        self::assertNotSame($messageA, $messageB);
    }

    #[RequiresPhpExtension('intl')]
    public function test_custom_locale_is_used(): void
    {
        $message = FakeNodeMessage::new(body: 'un message: {value, spellout}')
            ->withParameter('value', '42')
            ->withLocale('fr');

        $expected = 'un message: quarante-deux';

        self::assertSame($expected, $message->toString());
        self::assertSame($expected, (string)$message);
    }

    public function test_message_with_no_code_returns_unknown(): void
    {
        $originalMessage = new class () implements Message {
            public function body(): string
            {
                return 'some message';
            }
        };

        $message = FakeNodeMessage::new(message: $originalMessage);

        self::assertSame('unknown', $message->code());
    }
}
