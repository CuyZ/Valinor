<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception;

use RuntimeException;

/** @internal */
final class UnexpectedToken extends RuntimeException implements InvalidType
{
    public function __construct(string $token)
    {
        parent::__construct("Unexpected token `$token`, expected a valid type.");
    }
}
