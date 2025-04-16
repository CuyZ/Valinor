<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class TypeTreeMapperError extends RuntimeException implements MappingError
{
    private Messages $errors;

    /**
     * @param non-empty-list<NodeMessage> $errors
     */
    public function __construct(mixed $source, Type $type, array $errors)
    {
        $this->errors = new Messages(...$errors);

        $errorsCount = count($errors);

        if ($errorsCount === 1) {
            $body = $errors[0]
                ->withParameter('root_type', $type->toString())
                ->withBody("Could not map type `{root_type}`. An error occurred at path {node_path}: {original_message}")
                ->toString();
        } else {
            $source = ValueDumper::dump($source);
            $body = "Could not map type `{$type->toString()}` with value $source. A total of $errorsCount errors were encountered.";
        }

        parent::__construct($body, 1617193185);
    }

    public function messages(): Messages
    {
        return $this->errors;
    }
}
