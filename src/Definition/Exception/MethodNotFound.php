<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use LogicException;

final class MethodNotFound extends LogicException
{
    public function __construct(string $method)
    {
        parent::__construct(
            "The method `$method` does not exist.",
            1510936269
        );
    }
}
