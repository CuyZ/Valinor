<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use Countable;
use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageFormatter;
use Iterator;
use IteratorAggregate;

use function array_filter;
use function count;
use function iterator_to_array;

/**
 * Container for messages representing errors detected during mapping.
 *
 * Message formatters can be added and will be applied on all messages.
 *
 * ```
 * try {
 *     return (new \CuyZ\Valinor\MapperBuilder())
 *         ->mapper()
 *         ->map(SomeClass::class, [ … ]);
 * } catch (\CuyZ\Valinor\Mapper\MappingError $error) {
 *     // Get a flattened list of all messages detected during mapping
 *     $messages = $error->messages();
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
 *     foreach ($messages as $message) {
 *         echo $message;
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
    /** @var array<NodeMessage> */
    private array $messages;

    /** @var array<MessageFormatter> */
    private array $formatters = [];

    /**
     * @internal
     * @no-named-arguments
     */
    public function __construct(NodeMessage ...$messages)
    {
        $this->messages = $messages;
    }

    /** @pure */
    public function errors(): self
    {
        $clone = clone $this;
        $clone->messages = array_filter($this->messages, fn (NodeMessage $message) => $message->isError());

        return $clone;
    }

    /** @pure */
    public function formatWith(MessageFormatter ...$formatters): self
    {
        $clone = clone $this;
        $clone->formatters = [...$clone->formatters, ...$formatters];

        return $clone;
    }

    /**
     * @pure
     * @return list<NodeMessage>
     */
    public function toArray(): array
    {
        /** @var list<NodeMessage> */
        return iterator_to_array($this);
    }

    /** @pure */
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
