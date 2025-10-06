<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class ArrayExpectedCommaOrClosingBracket extends RuntimeException implements InvalidType
{
    public function __construct(string $arrayType, Type $subtype)
    {
        parent::__construct("Expected comma or closing bracket after `$arrayType<{$subtype->toString()}`.");
    }
}
