<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotGetInvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Exception\DuplicatedNodeChild;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class NodeTest extends TestCase
{
    public function test_node_leaf_values_can_be_retrieved(): void
    {
        $value = 'some node value';
        $type = FakeType::thatWillAccept($value);

        $shell = Shell::root($type, []);
        $node = Node::leaf($shell, $value);

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
        $this->expectExceptionMessage("Value of type `string` is not accepted by type `$type`.");

        $shell = Shell::root($type, []);
        Node::leaf($shell, 'foo');
    }

    public function test_node_branch_values_can_be_retrieved(): void
    {
        $value = ['foo', 'bar'];
        $type = FakeType::thatWillAccept($value);

        $shell = Shell::root($type, $value);

        $typeChildA = FakeType::thatWillAccept('foo');
        $typeChildB = FakeType::thatWillAccept('bar');
        $attributesChildA = new FakeAttributes();
        $attributesChildB = new FakeAttributes();

        $childA = $shell->child('foo', $typeChildA, 'foo', $attributesChildA);
        $childB = $shell->child('bar', $typeChildB, 'bar', $attributesChildB);

        $children = [
            'foo' => Node::leaf($childA, 'foo'),
            'bar' => Node::leaf($childB, 'bar'),
        ];

        $node = Node::branch($shell, $value, $children);

        self::assertSame($type, $node->type());
        self::assertSame($value, $node->value());
        self::assertTrue($node->isValid());
        self::assertSame($children, $node->children());

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
        $type = FakeType::thatWillAccept('foo');

        $this->expectException(DuplicatedNodeChild::class);
        $this->expectExceptionCode(1634045114);
        $this->expectExceptionMessage('The child `foo` is duplicated in the branch.');

        $shell = Shell::root($type, 'foo');
        $childA = $shell->child('foo', $type, 'foo');
        $childB = $shell->child('foo', $type, 'foo');

        $children = [
            Node::leaf($childA, 'foo'),
            Node::leaf($childB, 'foo'),
        ];

        Node::branch($shell, 'foo', $children);
    }

    public function test_node_branch_with_incorrect_value_throws_exception(): void
    {
        $type = new FakeType();

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage("Value of type `string` is not accepted by type `$type`.");

        $shell = Shell::root($type, []);
        Node::branch($shell, 'foo', []);
    }

    public function test_node_error_values_can_be_retrieved(): void
    {
        $message = new FakeErrorMessage();

        $shell = Shell::root(new FakeType(), []);
        $node = Node::error($shell, $message);

        self::assertSame([$message], $node->messages());
        self::assertFalse($node->isValid());
    }

    public function test_get_value_from_invalid_node_throws_exception(): void
    {
        $message = new FakeErrorMessage();

        $shell = Shell::root(new FakeType(), []);
        $node = Node::error($shell, $message);

        $this->expectException(CannotGetInvalidNodeValue::class);
        $this->expectExceptionCode(1630680246);
        $this->expectExceptionMessage('Trying to get value of an invalid node at path ``.');

        $node->value();
    }

    public function test_branch_node_with_invalid_child_is_invalid(): void
    {
        $shell = Shell::root(FakeType::thatWillAccept([]), []);

        $childA = $shell->child('foo', FakeType::thatWillAccept('foo'), 'foo');
        $childB = $shell->child('bar', FakeType::thatWillAccept('bar'), 'bar');

        $children = [
            'foo' => Node::leaf($childA, 'foo'),
            'bar' => Node::error($childB, new FakeErrorMessage()),
        ];

        $node = Node::branch($shell, [], $children);

        self::assertFalse($node->isValid());
    }

    public function test_node_with_value_returns_node_with_value(): void
    {
        $shell = Shell::root(FakeType::thatWillAccept('foo', 'bar'), 'foo');

        $nodeA = Node::leaf($shell, 'foo');
        $nodeB = $nodeA->withValue('bar');

        self::assertNotSame($nodeA, $nodeB);
        self::assertSame('bar', $nodeB->value());
        self::assertTrue($nodeB->isValid());
    }

    public function test_node_with_invalid_value_returns_invalid_node(): void
    {
        $type = FakeType::thatWillAccept('foo');
        $shell = Shell::root($type, 'foo');

        $this->expectException(InvalidNodeValue::class);
        $this->expectExceptionCode(1630678334);
        $this->expectExceptionMessage("Value of type `int` is not accepted by type `$type`.");

        Node::leaf($shell, 'foo')->withValue(1337);
    }

    public function test_node_with_messages_returns_node_with_messages(): void
    {
        $messageA = new FakeMessage();
        $messageB = new FakeMessage();

        $shell = Shell::root(FakeType::thatWillAccept('foo'), 'foo');

        $nodeA = Node::leaf($shell, 'foo');
        $nodeB = $nodeA->withMessage($messageA)->withMessage($messageB);

        self::assertNotSame($nodeA, $nodeB);
        self::assertSame([$messageA, $messageB], $nodeB->messages());
        self::assertTrue($nodeB->isValid());
    }

    public function test_node_with_error_message_returns_invalid_node(): void
    {
        $message = new FakeMessage();
        $errorMessage = new FakeErrorMessage();

        $shell = Shell::root(FakeType::thatWillAccept('foo'), 'foo');

        $nodeA = Node::leaf($shell, 'foo');
        $nodeB = $nodeA->withMessage($message)->withMessage($errorMessage);

        self::assertNotSame($nodeA, $nodeB);
        self::assertSame([$message, $errorMessage], $nodeB->messages());
        self::assertFalse($nodeB->isValid());
    }
}
