<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Scalar;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class ClassStringClosingBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(Type $type)
    {
        parent::__construct("The closing bracket is missing for the class string expression `class-string<{$type->toString()}>`.");
    }
}
