<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class TypeTreeMapperError extends RuntimeException implements MappingError
{
    public function __construct(private Node $node)
    {
        $errors = Messages::flattenFromNode($node)->errors();
        $errorsCount = count($errors);

        if ($errorsCount === 1) {
            $body = $errors
                ->toArray()[0]
                ->withParameter('root_type', $node->type())
                ->withBody("Could not map type `{root_type}`. An error occurred at path {node_path}: {original_message}")
                ->toString();
        } else {
            $source = ValueDumper::dump($node->sourceValue());
            $body = "Could not map type `{$node->type()}` with value $source. A total of $errorsCount errors were encountered.";
        }

        parent::__construct($body, 1617193185);
    }

    public function node(): Node
    {
        return $this->node;
    }
}
