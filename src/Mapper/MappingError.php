<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class MappingError extends RuntimeException
{
    private Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;

        $source = ValueDumper::dump($node->sourceValue());

        $errors = (new MessagesFlattener($node))->errors();
        $errorsCount = count($errors);

        $body = "Could not map type `{$node->type()}` with value $source. A total of $errorsCount errors were encountered.";

        if ($errorsCount === 1) {
            $body = $errors->getIterator()->current()
                ->withParameter('root_type', $node->type())
                ->withBody("Could not map type `{root_type}`. An error occurred at path {node_path}: {original_message}")
                ->toString();
        }

        parent::__construct($body, 1617193185);
    }

    public function node(): Node
    {
        return $this->node;
    }
}
