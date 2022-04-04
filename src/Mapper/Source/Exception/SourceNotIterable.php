<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class SourceNotIterable extends RuntimeException implements SourceException
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Invalid source $value, expected an iterable.",
            1566307291
        );
    }
}
