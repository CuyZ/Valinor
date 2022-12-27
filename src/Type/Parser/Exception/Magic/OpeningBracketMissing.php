<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Magic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class OpeningBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(string $symbol)
    {
        parent::__construct(
            "The opening bracket is missing for `$symbol<...>`.",
            1618994728
        );
    }
}
