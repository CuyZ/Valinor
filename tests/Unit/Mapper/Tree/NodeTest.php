<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeHasNoMappedValue;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceValueWasNotFilled;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\FakeNode;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use PHPUnit\Framework\TestCase;

final class NodeTest extends TestCase
{
    public function test_properties_can_be_accessed(): void
    {
        $message = new FakeMessage('some message');
        $child = FakeNode::any();

        $node = new Node(
            true,
            'nodeName',
            'some.node.path',
            'string',
            true,
            'some source value',
            'some value',
            [$message],
            [$child]
        );

        self::assertSame(true, $node->isRoot());
        self::assertSame('nodeName', $node->name());
        self::assertSame('some.node.path', $node->path());
        self::assertSame('string', $node->type());
        self::assertSame('some source value', $node->sourceValue());
        self::assertSame('some value', $node->value());
        self::assertSame('some value', $node->mappedValue());
        self::assertSame(true, $node->isValid());
        self::assertSame('some message', (string)$node->messages()[0]);
        self::assertSame([$child], $node->children());
    }

    public function test_error_message_makes_node_not_valid(): void
    {
        $message = new FakeErrorMessage();

        $node = new Node(
            true,
            'nodeName',
            'some.node.path',
            'string',
            true,
            'some source value',
            'some value',
            [$message],
            []
        );

        self::assertSame(false, $node->isValid());
    }

    public function test_get_source_value_not_filled_throws_exception(): void
    {
        $this->expectException(SourceValueWasNotFilled::class);
        $this->expectExceptionCode(1657466107);
        $this->expectExceptionMessage('Source was not filled at path `some.node.path`; use method `$node->sourceFilled()`.');

        (new Node(
            true,
            'nodeName',
            'some.node.path',
            'string',
            false,
            null,
            'some value',
            [],
            []
        ))->sourceValue();
    }

    public function test_get_mapped_value_from_invalid_node_throws_exception(): void
    {
        $this->expectException(InvalidNodeHasNoMappedValue::class);
        $this->expectExceptionCode(1657466305);
        $this->expectExceptionMessage('Cannot get mapped value for invalid node at path `some.node.path`; use method `$node->isValid()`.');

        (new Node(
            true,
            'nodeName',
            'some.node.path',
            'string',
            true,
            'some source value',
            null,
            [new FakeErrorMessage()],
            []
        ))->mappedValue();
    }
}
