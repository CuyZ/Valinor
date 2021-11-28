<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception;

use RuntimeException;

final class ImpossibleParsing extends RuntimeException implements InvalidType
{
    public function __construct(string $raw)
    {
        parent::__construct(
            "The type `$raw` could not be parsed.",
            1585373891
        );
    }
}
