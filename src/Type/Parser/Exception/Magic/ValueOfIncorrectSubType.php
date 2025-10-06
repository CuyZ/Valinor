<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Magic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class ValueOfIncorrectSubType extends RuntimeException implements InvalidType
{
    public function __construct(Type $type)
    {
        parent::__construct("Invalid subtype `value-of<{$type->toString()}>`, it should be a `BackedEnum`.");
    }
}
