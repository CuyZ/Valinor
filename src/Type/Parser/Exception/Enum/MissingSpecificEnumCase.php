<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Enum;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;
use UnitEnum;

/** @internal */
final class MissingSpecificEnumCase extends RuntimeException implements InvalidType
{
    /**
     * @param class-string<UnitEnum> $enumName
     */
    public function __construct(string $enumName)
    {
        parent::__construct("Missing specific case for enum `$enumName::?` (cannot be `*`).");
    }
}
