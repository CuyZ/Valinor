<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener;
use CuyZ\Valinor\Mapper\Tree\Node;
use RuntimeException;

/** @api */
final class MappingError extends RuntimeException
{
    private Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;

        $firstMessage = '';
        foreach (new MessagesFlattener($node) as $message) {
            $firstMessage = $message->withBody(" First error is:\nPath: {node_path}\nGiven value: {original_value}\nError: {original_message}")->__toString();
            break;
        }

        parent::__construct(
            "Could not map type `{$node->type()}` with the given source.{$firstMessage}",
            1617193185
        );
    }

    public function node(): Node
    {
        return $this->node;
    }
}
