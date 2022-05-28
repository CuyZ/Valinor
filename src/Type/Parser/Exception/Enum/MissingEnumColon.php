<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Enum;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;
use UnitEnum;

/** @internal */
final class MissingEnumColon extends RuntimeException implements InvalidType
{
    /**
     * @param class-string<UnitEnum> $enumName
     */
    public function __construct(string $enumName, string $case)
    {
        if ($case === ':') {
            $case = '?';
        }

        parent::__construct(
            "Missing second colon symbol for enum `$enumName::$case`.",
            1653468435
        );
    }
}
