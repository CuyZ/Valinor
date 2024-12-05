<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Builder\TreeNode;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use Throwable;

final class FakeTreeNode
{
    public static function any(): TreeNode
    {
        return self::leaf(FakeType::permissive(), []);
    }

    public static function leaf(Type $type, mixed $value): TreeNode
    {
        $shell = FakeShell::new($type, $value);

        return TreeNode::leaf($shell, $value);
    }

    /**
     * @param array<array{name?: string, type?: Type, value?: mixed, attributes?: Attributes, message?: Message}> $children
     */
    public static function branch(array $children, ?Type $type = null, mixed $value = null): TreeNode
    {
        $shell = FakeShell::new($type ?? FakeType::permissive(), $value);
        $nodes = [];

        foreach ($children as $key => $child) {
            $childValue = $child['value'] ?? [];
            $childShell = $shell->child(
                $child['name'] ?? (string)$key,
                $child['type'] ?? FakeType::permissive(),
                $child['attributes'] ?? new Attributes(),
            )->withValue($childValue);

            $node = TreeNode::leaf($childShell, $childValue);

            if (isset($child['message'])) {
                $node = $node->withMessage($child['message']);
            }

            $nodes[] = $node;
        }

        return TreeNode::branch($shell, $value, $nodes);
    }

    /**
     * @param Throwable&Message $error
     */
    public static function error(?Throwable $error = null): TreeNode
    {
        $shell = FakeShell::new(FakeType::permissive(), []);

        return TreeNode::error($shell, $error ?? new FakeErrorMessage());
    }
}
