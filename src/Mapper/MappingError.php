<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Node;
use RuntimeException;

/** @api */
final class MappingError extends RuntimeException
{
    private Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;

        parent::__construct(
            "Could not map type `{$node->type()}` with the given source.",
            1617193185
        );
    }

    public function node(): Node
    {
        return $this->node;
    }
}
