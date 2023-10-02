<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class InvalidMappingTypeSignature extends RuntimeException
{
    public function __construct(string $raw, InvalidType $exception)
    {
        parent::__construct(
            "Could not parse the type `$raw` that should be mapped: {$exception->getMessage()}",
            1630959692,
            $exception
        );
    }
}
