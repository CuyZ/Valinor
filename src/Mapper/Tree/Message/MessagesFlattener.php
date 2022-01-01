<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\NodeTraverser;
use IteratorAggregate;
use Traversable;

use function array_filter;

/**
 * Will recursively flatten messages of a node and all its children.
 *
 * This helper can for instance be used when errors occurred during a mapping to
 * flatten all caught errors into a basic array of string that can then easily
 * be used to inform the user of what is wrong.
 *
 * ```
 * try {
 *     // …
 * } catch(MappingError $error) {
 *     $messages = (new MessagesFlattener($error->node()))->errors();
 *
 *     foreach ($messages as $message) {
 *         echo $message;
 *     }
 * }
 * ```
 *
 * @implements IteratorAggregate<NodeMessage>
 */
final class MessagesFlattener implements IteratorAggregate
{
    /** @var array<NodeMessage> */
    private array $messages = [];

    public function __construct(Node $node)
    {
        $grouped = (new NodeTraverser(
            fn (Node $node) => $node->messages()
        ))->traverse($node);

        foreach ($grouped as $messages) {
            $this->messages = [...$this->messages, ...$messages];
        }
    }

    public function errors(): self
    {
        $clone = clone $this;
        $clone->messages = array_filter($clone->messages, fn (NodeMessage $message) => $message->isError());

        return $clone;
    }

    /**
     * @return Traversable<NodeMessage>
     */
    public function getIterator(): Traversable
    {
        yield from $this->messages;
    }
}
