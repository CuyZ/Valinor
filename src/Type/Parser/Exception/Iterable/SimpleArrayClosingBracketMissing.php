<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class SimpleArrayClosingBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(Type $subType)
    {
        parent::__construct("The closing bracket is missing for the array expression `{$subType->toString()}[]`.");
    }
}
