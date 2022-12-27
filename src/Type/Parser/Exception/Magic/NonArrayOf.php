<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Magic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class NonArrayOf extends RuntimeException implements InvalidType
{
    public function __construct(string $symbol, Type $type)
    {
        parent::__construct(
            "The type inside of `$symbol<{$type->toString()}>` is not an array.",
            1618994728
        );
    }
}
