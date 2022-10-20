<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class SourceNotIterable extends RuntimeException implements SourceException
{
    public function __construct(mixed $value)
    {
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Invalid source $value, expected an iterable.",
            1566307291
        );
    }
}
