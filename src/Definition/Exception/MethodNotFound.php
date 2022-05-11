<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use LogicException;

/** @internal */
final class MethodNotFound extends LogicException
{
    public function __construct(string $method)
    {
        parent::__construct(
            "The method `$method` does not exist.",
            1_510_936_269
        );
    }
}
