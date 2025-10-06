<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use RuntimeException;

/** @internal */
final class MissingObjectImplementationRegistration extends RuntimeException
{
    public function __construct(string $name, FunctionDefinition $functionDefinition)
    {
        parent::__construct("No implementation of `$name` found with return type `{$functionDefinition->returnType->toString()}` of `$functionDefinition->signature`.");
    }
}
