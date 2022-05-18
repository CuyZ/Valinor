<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotGetInvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Exception\DuplicatedNodeChild;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeNode;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NodeTest extends TestCase
{
    public function test_node_leaf_values_can_be_retrieved(): void
    {
        $type = FakeType::permissive();
        $value = 'some node value';

        $node = FakeNode::leaf($type, $value);

        self::assertSame($type, $node->type());
        self::assertSame($value, $node->value());
        self::assertTrue($node->isRoot());
        self::assertTrue($node->isValid());
    }

    public function test_node_leaf_with_incorrect_value_throws_exception(): void
    {
        $type = new FakeType();

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage("Value 'foo' does not match type `$type`.");

        FakeNode::leaf($type, 'foo');
    }

    public function test_node_branch_values_can_be_retrieved(): void
    {
        $typeChildA = FakeType::permissive();
        $typeChildB = FakeType::permissive();
        $attributesChildA = new FakeAttributes();
        $attributesChildB = new FakeAttributes();

        $children = FakeNode::branch([
            'foo' => ['type' => $typeChildA, 'value' => 'foo', 'attributes' => $attributesChildA],
            'bar' => ['type' => $typeChildB, 'value' => 'bar', 'attributes' => $attributesChildB],
        ])->children();

        self::assertSame('foo', $children['foo']->name());
        self::assertSame('foo', $children['foo']->path());
        self::assertFalse($children['foo']->isRoot());
        self::assertSame($attributesChildA, $children['foo']->attributes());
        self::assertSame('bar', $children['bar']->name());
        self::assertSame('bar', $children['bar']->path());
        self::assertFalse($children['bar']->isRoot());
        self::assertSame($attributesChildB, $children['bar']->attributes());
    }

    public function test_node_branch_with_duplicated_child_name_throws_exception(): void
    {
        $this->expectException(DuplicatedNodeChild::class);
        $this->expectExceptionCode(1634045114);
        $this->expectExceptionMessage('The child `foo` is duplicated in the branch.');

        FakeNode::branch([
            ['name' => 'foo'],
            ['name' => 'foo'],
        ]);
    }

    public function test_node_branch_with_incorrect_value_throws_exception(): void
    {
        $type = new FakeType();

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage("Value 'foo' does not match type `$type`.");

        FakeNode::branch([], $type, 'foo');
    }

    public function test_node_error_values_can_be_retrieved(): void
    {
        $message = new FakeErrorMessage();
        $node = FakeNode::error($message);

        self::assertFalse($node->isValid());
        self::assertSame('some error message', (string)$node->messages()[0]);
    }

    public function test_get_value_from_invalid_node_throws_exception(): void
    {
        $node = FakeNode::error();

        $this->expectException(CannotGetInvalidNodeValue::class);
        $this->expectExceptionCode(1630680246);
        $this->expectExceptionMessage('Trying to get value of an invalid node at path ``.');

        $node->value();
    }

    public function test_branch_node_with_invalid_child_is_invalid(): void
    {
        $node = FakeNode::branch([
            'foo' => [],
            'bar' => ['message' => new FakeErrorMessage()],
        ]);

        self::assertFalse($node->isValid());
    }

    public function test_node_with_value_returns_node_with_value(): void
    {
        $nodeA = FakeNode::any();
        $nodeB = $nodeA->withValue('bar');

        self::assertNotSame($nodeA, $nodeB);
        self::assertSame('bar', $nodeB->value());
        self::assertTrue($nodeB->isValid());
    }

    public function test_node_with_invalid_value_returns_invalid_node(): void
    {
        $type = FakeType::accepting('foo');

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage("Value 1337 does not match type `$type`.");

        FakeNode::leaf($type, 'foo')->withValue(1337);
    }

    public function test_node_with_invalid_value_for_object_type_returns_invalid_node(): void
    {
        $object = new stdClass();
        $type = FakeObjectType::accepting($object);

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage('Invalid value 1337.');

        FakeNode::leaf($type, $object)->withValue(1337);
    }

    public function test_node_with_messages_returns_node_with_messages(): void
    {
        $messageA = new FakeMessage('some message A');
        $messageB = new FakeMessage('some message B');

        $nodeA = FakeNode::any();
        $nodeB = $nodeA->withMessage($messageA)->withMessage($messageB);

        self::assertNotSame($nodeA, $nodeB);
        self::assertTrue($nodeB->isValid());
        self::assertSame('some message A', (string)$nodeB->messages()[0]);
        self::assertSame('some message B', (string)$nodeB->messages()[1]);
    }

    public function test_node_with_error_message_returns_invalid_node(): void
    {
        $message = new FakeMessage();
        $errorMessage = new FakeErrorMessage();

        $nodeA = FakeNode::any();
        $nodeB = $nodeA->withMessage($message)->withMessage($errorMessage);

        self::assertNotSame($nodeA, $nodeB);
        self::assertFalse($nodeB->isValid());
        self::assertSame('some message', (string)$nodeB->messages()[0]);
        self::assertSame('some error message', (string)$nodeB->messages()[1]);
    }
}
