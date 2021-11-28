<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

use function get_debug_type;

final class SourceNotIterable extends RuntimeException implements SourceException
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $type = get_debug_type($value);

        parent::__construct(
            "The configuration is not an iterable but of type `$type`.",
            1566307291
        );
    }
}
