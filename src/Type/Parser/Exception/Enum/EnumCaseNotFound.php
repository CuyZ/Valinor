<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Enum;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;
use UnitEnum;

/** @internal */
final class EnumCaseNotFound extends RuntimeException implements InvalidType
{
    /**
     * @param class-string<UnitEnum> $enumName
     */
    public function __construct(string $enumName, string $pattern)
    {
        $message = str_contains($pattern, '*')
            ? "Cannot find enum case with pattern `$enumName::$pattern`."
            : "Unknown enum case `$enumName::$pattern`.";

        parent::__construct($message, 1653468428);
    }
}
