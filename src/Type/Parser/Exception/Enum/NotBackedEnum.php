<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Enum;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class NotBackedEnum extends RuntimeException implements InvalidType
{
    public function __construct(string $enumName)
    {
        parent::__construct(
            "`$enumName` is not BackedEnum.",
            1653468439
        );
    }
}
