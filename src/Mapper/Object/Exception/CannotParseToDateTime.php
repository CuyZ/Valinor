<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use DateTimeInterface;
use RuntimeException;

/** @api */
final class CannotParseToDateTime extends RuntimeException implements Message
{
    /**
     * @param class-string<DateTimeInterface> $dateTimeClassName
     */
    public function __construct(string $datetime, string $dateTimeClassName)
    {
        parent::__construct(
            "Impossible to convert `$datetime` to `$dateTimeClassName`.",
            1630686564
        );
    }
}
