<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\FakeNode;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeNodeMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\Formatter\FakeMessageFormatter;
use PHPUnit\Framework\TestCase;

use function count;

final class MessagesTest extends TestCase
{
    public function test_iterator_yield_correct_messages(): void
    {
        $messageA = FakeNodeMessage::new();
        $messageB = FakeNodeMessage::new();

        $messages = new Messages($messageA, $messageB);

        self::assertSame([$messageA, $messageB], [...$messages]);
    }

    public function test_to_array_yield_correct_messages(): void
    {
        $messageA = FakeNodeMessage::new();
        $messageB = FakeNodeMessage::new();

        $messages = new Messages($messageA, $messageB);

        self::assertSame([$messageA, $messageB], $messages->toArray());
    }

    public function test_count_messages_return_correct_number(): void
    {
        $messages = new Messages(FakeNodeMessage::new(), FakeNodeMessage::new());

        self::assertSame(2, count($messages));
    }

    public function test_filter_errors_returns_only_errors(): void
    {
        $messages = new Messages(
            FakeNodeMessage::new(),
            $errorMessage = FakeNodeMessage::new(message: new FakeErrorMessage()),
            FakeNodeMessage::new(),
        );

        $errors = $messages->errors();

        self::assertNotSame($messages, $errors);
        self::assertSame([$errorMessage], $errors->toArray());
    }

    public function test_formatter_are_used_on_messages(): void
    {
        $messages = new Messages(FakeNodeMessage::new(body: 'some message'));

        $formatterA = FakeMessageFormatter::withPrefix('prefixA /');
        $formatterB = FakeMessageFormatter::withPrefix('prefixB /');
        $formatterC = FakeMessageFormatter::withPrefix('prefixC /');

        $formattedMessages = $messages
            ->formatWith($formatterA, $formatterB)
            ->formatWith($formatterC);

        self::assertNotSame($messages, $formattedMessages);
        self::assertSame('prefixC / prefixB / prefixA / some message', $formattedMessages->toArray()[0]->body());
    }

    public function test_flatten_from_node_recursively_fetches_all_messages(): void
    {
        $node = new Node(
            true,
            'nodeName',
            'some.node.path',
            'string',
            true,
            'some source value',
            'some value',
            [
                new FakeMessage('message A'),
                new FakeErrorMessage('message B'),
            ],
            [
                'foo' => FakeNode::withMessage(new FakeMessage('message C')),
                'bar' => FakeNode::withMessage(new FakeErrorMessage('message D')),
            ]
        );

        $messages = Messages::flattenFromNode($node)->toArray();

        self::assertSame('message A', $messages[0]->toString());
        self::assertSame('message B', $messages[1]->toString());
        self::assertSame('message C', $messages[2]->toString());
        self::assertSame('message D', $messages[3]->toString());
    }
}
