<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Magic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class ValueOfOpeningBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct()
    {
        parent::__construct("The opening bracket is missing for `value-of<...>`.");
    }
}
