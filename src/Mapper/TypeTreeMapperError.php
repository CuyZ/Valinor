<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class TypeTreeMapperError extends RuntimeException implements MappingError
{
    private Messages $messages;

    private string $type;

    private mixed $source;

    /**
     * @param non-empty-list<NodeMessage> $messages
     */
    public function __construct(mixed $source, string $type, array $messages)
    {
        $this->messages = new Messages(...$messages);
        $this->type = $type;
        $this->source = $source;

        $errorsCount = count($messages);

        if ($errorsCount === 1) {
            $body = $messages[0]
                ->withParameter('root_type', $type)
                ->withBody("Could not map type `{root_type}`. An error occurred at path {node_path}: {original_message}")
                ->toString();
        } else {
            $source = ValueDumper::dump($source);
            $body = "Could not map type `$type` with value $source. A total of $errorsCount errors were encountered.";
        }

        parent::__construct($body, 1617193185);
    }

    public function messages(): Messages
    {
        return $this->messages;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function source(): mixed
    {
        return $this->source;
    }
}
