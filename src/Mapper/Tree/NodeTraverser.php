<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

/**
 * @api
 *
 * @template T
 */
final class NodeTraverser
{
    /** @var callable(Node): T */
    private $callback;

    /**
     * @param callable(Node): T $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return iterable<T>
     */
    public function traverse(Node $node): iterable
    {
        return $this->recurse($node);
    }

    /**
     * @return iterable<T>
     */
    private function recurse(Node $node): iterable
    {
        yield ($this->callback)($node);

        foreach ($node->children() as $child) {
            yield from $this->recurse($child);
        }
    }
}
