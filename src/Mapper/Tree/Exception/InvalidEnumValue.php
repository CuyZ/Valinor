<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use BackedEnum;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;
use UnitEnum;

use function array_map;
use function implode;

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
                return ValueDumper::dump($case instanceof BackedEnum ? $case->value : $case->name);
            },
            $enumName::cases()
        );

        $values = implode(', ', $values);
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Invalid value $value, it must be one of $values.",
            1633093113
        );
    }
}
