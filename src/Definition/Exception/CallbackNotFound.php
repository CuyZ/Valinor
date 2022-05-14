<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use RuntimeException;

/** @internal */
final class CallbackNotFound extends RuntimeException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "The callback associated to `{$function->signature()}` could not be found.",
            1647523495
        );
    }
}
