<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use Throwable;

final class FakeNode
{
    public static function any(): Node
    {
        return self::leaf(FakeType::permissive(), []);
    }

    /**
     * @param mixed $value
     */
    public static function leaf(Type $type, $value): Node
    {
        $shell = FakeShell::new($type, $value);

        return Node::leaf($shell, $value);
    }

    /**
     * @param array<array{name?: string, type?: Type, value?: mixed, attributes?: Attributes, message?: Message}> $children
     * @param mixed $value
     */
    public static function branch(array $children, Type $type = null, $value = null): Node
    {
        $shell = FakeShell::new($type ?? FakeType::permissive(), $value);
        $nodes = [];

        foreach ($children as $key => $child) {
            $childValue = $child['value'] ?? [];

            $node = Node::leaf($shell->child(
                $child['name'] ?? (string)$key,
                $child['type'] ?? FakeType::permissive(),
                $childValue,
                $child['attributes'] ?? new FakeAttributes(),
            ), $childValue);

            if (isset($child['message'])) {
                $node = $node->withMessage($child['message']);
            }

            $nodes[] = $node;
        }

        return Node::branch($shell, $value, $nodes);
    }

    /**
     * @param Throwable&Message $error
     */
    public static function error(Throwable $error = null): Node
    {
        $shell = FakeShell::new(FakeType::permissive(), []);

        return Node::error($shell, $error ?? new FakeErrorMessage());
    }
}
