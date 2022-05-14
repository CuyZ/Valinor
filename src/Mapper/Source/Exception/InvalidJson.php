<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

/** @internal */
final class InvalidJson extends RuntimeException implements SourceException
{
    public function __construct()
    {
        parent::__construct(
            "The given value is not a valid JSON entry.",
            1566307185
        );
    }
}
