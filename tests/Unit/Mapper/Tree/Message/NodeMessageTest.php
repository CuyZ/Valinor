<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\FakeNode;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use PHPUnit\Framework\TestCase;

final class NodeMessageTest extends TestCase
{
    public function test_node_properties_can_be_accessed(): void
    {
        $originalMessage = new FakeMessage();

        $message = new NodeMessage(FakeNode::any(), $originalMessage);

        self::assertSame('nodeName', $message->node()->name());
        self::assertSame('some.node.path', $message->node()->path());
        self::assertSame('string', $message->node()->type());
        self::assertSame('some source value', $message->node()->sourceValue());
        self::assertSame('some value', $message->node()->mappedValue());
        self::assertSame($originalMessage, $message->originalMessage());
        self::assertFalse($message->isError());
    }

    public function test_message_is_error_if_original_message_is_throwable(): void
    {
        $originalMessage = new FakeErrorMessage();
        $message = FakeNodeMessage::withMessage($originalMessage);

        self::assertTrue($message->isError());
        self::assertSame('1652883436', $message->code());
        self::assertSame('some error message', $message->body());
    }

    public function test_parameters_are_replaced_in_body(): void
    {
        $originalMessage = (new FakeMessage('some original message'))->withParameters(['some_parameter' => 'some parameter value']);

        $message = new NodeMessage(FakeNode::any(), $originalMessage);
        $message = $message->withBody('{message_code} / {node_name} / {node_path} / {node_type} / {source_value} / {original_message} / {some_parameter}');

        $expected = "some_code / nodeName / some.node.path / `string` / 'some source value' / some original message / some parameter value";

        self::assertSame($expected, $message->toString());
        self::assertSame($expected, (string)$message);
    }

    public function test_custom_parameters_are_replaced_in_body(): void
    {
        $originalMessage = new FakeMessage('some original message / {some_parameter}');

        $message = new NodeMessage(FakeNode::any(), $originalMessage);
        $message = $message->withParameter('some_parameter', 'some value');

        $expected = 'some original message / some value';

        self::assertSame($expected, $message->toString());
        self::assertSame($expected, (string)$message);
    }

    public function test_replaces_correct_original_message_if_throwable(): void
    {
        $originalMessage = new FakeErrorMessage('some error message');

        $message = FakeNodeMessage::withMessage($originalMessage);
        $message = $message->withBody('original: {original_message}');

        $expected = 'original: some error message';

        self::assertSame($expected, $message->toString());
        self::assertSame($expected, (string)$message);
    }

    public function test_custom_body_returns_clone(): void
    {
        $messageA = FakeNodeMessage::any();
        $messageB = $messageA->withBody('some other message');

        self::assertNotSame($messageA, $messageB);
    }

    public function test_add_parameter_returns_clone(): void
    {
        $messageA = FakeNodeMessage::any();
        $messageB = $messageA->withParameter('some_parameter', 'some value');

        self::assertNotSame($messageA, $messageB);
    }

    public function test_custom_locale_returns_clone(): void
    {
        $messageA = FakeNodeMessage::any();
        $messageB = $messageA->withLocale('fr');

        self::assertNotSame($messageA, $messageB);
    }

    /**
     * @requires extension intl
     */
    public function test_custom_locale_is_used(): void
    {
        $originalMessage = (new FakeMessage('un message: {value, spellout}'))->withParameters(['value' => '42']);

        $message = FakeNodeMessage::withMessage($originalMessage);
        $message = $message->withLocale('fr');

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

        $message = FakeNodeMessage::withMessage($originalMessage);

        self::assertSame('unknown', $message->code());
    }
}
