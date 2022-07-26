<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Mapper\Tree\Builder\TreeNode;
use CuyZ\Valinor\Mapper\Tree\Exception\DuplicatedNodeChild;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder\FakeTreeNode;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TreeNodeTest extends TestCase
{
    public function test_node_leaf_values_can_be_retrieved(): void
    {
        $type = FakeType::permissive();

        $shell = Shell::root($type, 'some source value');
        $node = TreeNode::leaf($shell, 'some value')->node();

        self::assertTrue($node->isRoot());
        self::assertSame('', $node->name());
        self::assertSame('*root*', $node->path());
        self::assertSame($type->toString(), $node->type());
        self::assertTrue($node->sourceFilled());
        self::assertSame('some source value', $node->sourceValue());
        self::assertTrue($node->isValid());
        self::assertSame('some value', $node->mappedValue());
    }

    public function test_node_leaf_with_incorrect_value_throws_exception(): void
    {
        $type = new FakeType();

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage("Value 'foo' does not match type `{$type->toString()}`.");

        FakeTreeNode::leaf($type, 'foo');
    }

    public function test_node_branch_values_can_be_retrieved(): void
    {
        $typeChildA = FakeType::permissive();
        $typeChildB = FakeType::permissive();
        $attributesChildA = new FakeAttributes();
        $attributesChildB = new FakeAttributes();

        $children = FakeTreeNode::branch([
            'foo' => ['type' => $typeChildA, 'value' => 'foo', 'attributes' => $attributesChildA],
            'bar' => ['type' => $typeChildB, 'value' => 'bar', 'attributes' => $attributesChildB],
        ])->children();

        self::assertSame('foo', $children['foo']->node()->name());
        self::assertSame('foo', $children['foo']->node()->path());
        self::assertFalse($children['foo']->node()->isRoot());
        self::assertSame('bar', $children['bar']->node()->name());
        self::assertSame('bar', $children['bar']->node()->path());
        self::assertFalse($children['bar']->node()->isRoot());
    }

    public function test_node_branch_with_duplicated_child_name_throws_exception(): void
    {
        $this->expectException(DuplicatedNodeChild::class);
        $this->expectExceptionCode(1634045114);
        $this->expectExceptionMessage('The child `foo` is duplicated in the branch.');

        FakeTreeNode::branch([
            ['name' => 'foo'],
            ['name' => 'foo'],
        ]);
    }

    public function test_node_branch_with_incorrect_value_throws_exception(): void
    {
        $type = new FakeType();

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage("Value 'foo' does not match type `{$type->toString()}`.");

        FakeTreeNode::branch([], $type, 'foo');
    }

    public function test_node_error_values_can_be_retrieved(): void
    {
        $message = new FakeErrorMessage();
        $node = FakeTreeNode::error($message);

        self::assertFalse($node->isValid());
        self::assertSame('some error message', (string)$node->node()->messages()[0]);
    }

    public function test_get_value_from_invalid_node_throws_exception(): void
    {
        $node = FakeTreeNode::error();

        $this->expectException(AssertionError::class);

        $node->value();
    }

    public function test_branch_node_with_invalid_child_is_invalid(): void
    {
        $node = FakeTreeNode::branch([
            'foo' => [],
            'bar' => ['message' => new FakeErrorMessage()],
        ]);

        self::assertFalse($node->isValid());
    }

    public function test_node_with_value_returns_node_with_value(): void
    {
        $nodeA = FakeTreeNode::any();
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
        $this->expectExceptionMessage("Value 1337 does not match type `{$type->toString()}`.");

        FakeTreeNode::leaf($type, 'foo')->withValue(1337);
    }

    public function test_node_with_invalid_value_for_object_type_returns_invalid_node(): void
    {
        $object = new stdClass();
        $type = FakeObjectType::accepting($object);

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage('Invalid value 1337.');

        FakeTreeNode::leaf($type, $object)->withValue(1337);
    }

    public function test_node_with_messages_returns_node_with_messages(): void
    {
        $messageA = new FakeMessage('some message A');
        $messageB = new FakeMessage('some message B');

        $nodeA = FakeTreeNode::any();
        $nodeB = $nodeA->withMessage($messageA)->withMessage($messageB);

        self::assertNotSame($nodeA, $nodeB);
        self::assertTrue($nodeB->isValid());
        self::assertSame('some message A', (string)$nodeB->node()->messages()[0]);
        self::assertSame('some message B', (string)$nodeB->node()->messages()[1]);
    }

    public function test_node_with_error_message_returns_invalid_node(): void
    {
        $message = new FakeMessage();
        $errorMessage = new FakeErrorMessage();

        $nodeA = FakeTreeNode::any();
        $nodeB = $nodeA->withMessage($message)->withMessage($errorMessage);

        self::assertNotSame($nodeA, $nodeB);
        self::assertFalse($nodeB->isValid());
        self::assertSame('some message', (string)$nodeB->node()->messages()[0]);
        self::assertSame('some error message', (string)$nodeB->node()->messages()[1]);
    }
}
