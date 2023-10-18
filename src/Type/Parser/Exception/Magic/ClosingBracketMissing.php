<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Magic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class ClosingBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(string $symbol)
    {
        parent::__construct(
            "The closing bracket is missing for `$symbol<...>`.",
            1618994728
        );
    }
}
