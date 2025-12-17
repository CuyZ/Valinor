<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use RuntimeException;

/** @internal */
final class PsrRequestParsedBodyIsObject extends RuntimeException
{
    public function __construct(object $parsedBody)
    {
        parent::__construct('HTTP request\'s body must be an array, got an instance of `' . $parsedBody::class . '`.');
    }
}
