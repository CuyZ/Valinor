<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeNode;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class NodeMessageTest extends TestCase
{
    public function test_node_properties_can_be_accessed(): void
    {
        $originalMessage = new FakeMessage();
        $type = FakeType::permissive();
        $attributes = new FakeAttributes();

        $node = FakeNode::branch([[
            'name' => 'foo',
            'type' => $type,
            'value' => 'some value',
            'attributes' => $attributes,
            'message' => $originalMessage
        ]])->children()['foo'];

        $message = new NodeMessage($node, $originalMessage);

        self::assertSame('foo', $message->name());
        self::assertSame('foo', $message->path());
        self::assertSame('some value', $message->value());
        self::assertSame($type, $message->type());
        self::assertSame($attributes, $message->attributes());
        self::assertSame($originalMessage, $message->originalMessage());
    }

    public function test_value_from_invalid_node_returns_null(): void
    {
        $originalMessage = new FakeErrorMessage();
        $node = FakeNode::leaf(FakeType::permissive(), 'foo')->withMessage($originalMessage);

        $message = new NodeMessage($node, $originalMessage);

        self::assertNull($message->value());
    }

    public function test_format_message_replaces_placeholders_with_default_values(): void
    {
        $originalMessage = new FakeMessage();
        $type = FakeType::permissive();

        $node = FakeNode::branch([[
            'name' => 'foo',
            'type' => $type,
            'value' => 'some value',
            'message' => $originalMessage
        ]])->children()['foo'];

        $message = new NodeMessage($node, $originalMessage);
        $text = $message->format('%1$s / %2$s / %3$s / %4$s / %5$s');

        self::assertSame("some_code / some message / $type / foo / foo", $text);
    }

    public function test_format_message_replaces_placeholders_with_given_values(): void
    {
        $originalMessage = new FakeMessage();
        $node = FakeNode::any();

        $message = new NodeMessage($node, $originalMessage);
        $text = $message->format('%1$s / %2$s', 'foo', 'bar');

        self::assertSame('foo / bar', $text);
    }
}
