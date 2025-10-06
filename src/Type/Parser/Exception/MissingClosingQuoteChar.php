<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception;

use RuntimeException;

/** @internal */
final class MissingClosingQuoteChar extends RuntimeException implements InvalidType
{
    public function __construct(string $value)
    {
        parent::__construct("Closing quote is missing for `$value`.");
    }
}
