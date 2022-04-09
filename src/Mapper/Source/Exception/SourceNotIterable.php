<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use CuyZ\Valinor\Utility\Polyfill;
use RuntimeException;

/** @internal */
final class SourceNotIterable extends RuntimeException implements SourceException
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $type = Polyfill::get_debug_type($value);

        parent::__construct(
            "The configuration is not an iterable but of type `$type`.",
            1566307291
        );
    }
}
