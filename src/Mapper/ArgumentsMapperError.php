<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class ArgumentsMapperError extends RuntimeException implements MappingError
{
    private Messages $errors;

    /**
     * @param list<NodeMessage> $errors
     */
    public function __construct(mixed $source, FunctionDefinition $function, array $errors)
    {
        $this->errors = new Messages(...$errors);

        $errorsCount = count($errors);

        if ($errorsCount === 1) {
            $body = $errors[0]
                ->withBody("Could not map arguments of `$function->signature`. An error occurred at path {node_path}: {original_message}")
                ->toString();
        } else {
            $source = ValueDumper::dump($source);
            $body = "Could not map arguments of `$function->signature` with value $source. A total of $errorsCount errors were encountered.";
        }

        parent::__construct($body, 1671115362);
    }

    public function messages(): Messages
    {
        return $this->errors;
    }
}
