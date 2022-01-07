<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use BackedEnum;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;
use UnitEnum;

use function array_map;
use function implode;
use function is_bool;
use function is_scalar;

/** @api */
final class InvalidEnumValue extends RuntimeException implements Message
{
    /**
     * @param class-string<UnitEnum> $enumName
     * @param mixed $value
     */
    public function __construct(string $enumName, $value)
    {
        $values = array_map(
            static function (UnitEnum $case) {
                return $case instanceof BackedEnum
                    ? $case->value
                    : $case->name;
            },
            $enumName::cases()
        );

        $values = implode('`, `', $values);

        if (! is_scalar($value) || is_bool($value)) {
            $value = get_debug_type($value);
        }

        parent::__construct(
            "Invalid value `$value`, it must be one of `$values`.",
            1633093113
        );
    }
}
