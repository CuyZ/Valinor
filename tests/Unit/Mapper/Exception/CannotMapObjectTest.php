<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Exception;

use CuyZ\Valinor\Mapper\Exception\CannotMapObject;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class CannotMapObjectTest extends TestCase
{
    public function test_describe_mapping_errors_lists_errors(): void
    {
        $message = new FakeMessage();
        $error = new FakeErrorMessage();

        $shell = Shell::root(FakeType::thatWillAccept([]), []);
        $childA = $shell->child('foo', FakeType::thatWillAccept('foo'), 'foo');
        $childB = $shell->child('bar', FakeType::thatWillAccept('bar'), 'bar');

        $children = [
            Node::leaf($childA, 'foo')->withMessage($message)->withMessage($error)->withMessage($message),
            Node::leaf($childB, 'bar')->withMessage($message)->withMessage($error)->withMessage($message),
        ];

        $node = Node::branch($shell, [], $children);

        $errors = (new CannotMapObject($node))->describe();

        self::assertSame([
            'foo' => [$error],
            'bar' => [$error],
        ], $errors);
    }
}
