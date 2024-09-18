<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Builder\TreeNode;
use CuyZ\Valinor\Mapper\Tree\Shell;
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

        $shell = Shell::root(new Settings(), $type, 'some source value');
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

    public function test_node_leaf_with_incorrect_value_is_invalid(): void
    {
        $type = new FakeType();

        $node = FakeTreeNode::leaf($type, 'foo');

        self::assertFalse($node->isValid());
    }

    public function test_node_branch_values_can_be_retrieved(): void
    {
        $typeChildA = FakeType::permissive();
        $typeChildB = FakeType::permissive();
        $attributesChildA = new Attributes();
        $attributesChildB = new Attributes();

        $node = FakeTreeNode::branch([
            'foo' => ['type' => $typeChildA, 'value' => 'foo', 'attributes' => $attributesChildA],
            'bar' => ['type' => $typeChildB, 'value' => 'bar', 'attributes' => $attributesChildB],
        ]);

        $childFoo = $node->node()->children()['foo'];
        $childBar = $node->node()->children()['bar'];

        self::assertSame('foo', $childFoo->name());
        self::assertSame('foo', $childFoo->path());
        self::assertSame('foo', $childFoo->sourceValue());
        self::assertSame(true, $childFoo->sourceFilled());
        self::assertFalse($childFoo->isRoot());
        self::assertSame('bar', $childBar->name());
        self::assertSame('bar', $childBar->path());
        self::assertSame('bar', $childBar->sourceValue());
        self::assertSame(true, $childBar->sourceFilled());
        self::assertFalse($childBar->isRoot());
    }

    public function test_node_branch_with_incorrect_value_throws_exception(): void
    {
        $type = new FakeType();

        $node = FakeTreeNode::branch([], $type, 'foo');

        self::assertFalse($node->isValid());
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

        $node = FakeTreeNode::leaf($type, 'foo')->withValue(1337);

        self::assertFalse($node->isValid());
    }

    public function test_node_with_invalid_value_for_object_type_returns_invalid_node(): void
    {
        $object = new stdClass();
        $type = FakeObjectType::accepting($object);

        $node = FakeTreeNode::leaf($type, $object)->withValue(1337);

        self::assertFalse($node->isValid());
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
