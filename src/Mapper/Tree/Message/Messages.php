<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use Countable;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\NodeTraverser;
use Iterator;
use IteratorAggregate;

use function array_filter;
use function array_values;
use function count;

/**
 * Contains instances of messages. Can be used to flatten all messages of a node
 * when a mapping error occurs.
 *
 * Message formatters can be added and will be applied on all messages.
 *
 * ```php
 * try {
 *     return (new \CuyZ\Valinor\MapperBuilder())
 *         ->mapper()
 *         ->map(SomeClass::class, [/* … * /]);
 * } catch (\CuyZ\Valinor\Mapper\MappingError $error) {
 *     // Get a flatten list of all messages through the whole nodes tree
 *     $messages = \CuyZ\Valinor\Mapper\Tree\Message\Messages::flattenFromNode(
 *         $error->node()
 *     );
 *
 *     // Formatters can be added and will be applied on all messages
 *     $messages = $messages->formatWith(
 *         new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter([
 *             // …
 *         ]),
 *         (new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter())
 *             ->withTranslations([
 *                 // …
 *             ])
 *     );
 *
 *     // If only errors are wanted, they can be filtered
 *     $errors = $messages->errors();
 *
 *     foreach ($errors as $errorMessage) {
 *         // …
 *     }
 * }
 * ```
 *
 * @api
 *
 * @implements IteratorAggregate<int, NodeMessage>
 */
final class Messages implements IteratorAggregate, Countable
{
    /** @var list<NodeMessage> */
    private array $messages;

    /** @var array<MessageFormatter> */
    private array $formatters = [];

    /**
     * @no-named-arguments
     */
    public function __construct(NodeMessage ...$messages)
    {
        $this->messages = $messages;
    }

    public static function flattenFromNode(Node $node): self
    {
        $nodeMessages = (new NodeTraverser(
            fn (Node $node) => $node->messages()
        ))->traverse($node);

        $messages = [];

        foreach ($nodeMessages as $messagesGroup) {
            $messages = [...$messages, ...$messagesGroup];
        }

        return new self(...$messages);
    }

    public function errors(): self
    {
        $clone = clone $this;
        $clone->messages = array_values(
            array_filter($clone->messages, fn (NodeMessage $message) => $message->isError())
        );

        return $clone;
    }

    public function formatWith(MessageFormatter ...$formatters): self
    {
        $clone = clone $this;
        $clone->formatters = [...$clone->formatters, ...$formatters];

        return $clone;
    }

    /**
     * @return list<NodeMessage>
     */
    public function toArray(): array
    {
        return [...$this];
    }

    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * @return Iterator<int, NodeMessage>
     */
    public function getIterator(): Iterator
    {
        foreach ($this->messages as $message) {
            foreach ($this->formatters as $formatter) {
                $message = $formatter->format($message);
            }

            yield $message;
        }
    }
}
