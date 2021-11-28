<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\MethodDefinition;
use RuntimeException;

final class ConstructorMethodIsNotStatic extends RuntimeException
{
    public function __construct(MethodDefinition $method)
    {
        parent::__construct(
            "Invalid constructor method `{$method->signature()}`: it is neither the constructor nor a static constructor.",
            1634044370
        );
    }
}
