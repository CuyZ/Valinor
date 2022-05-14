<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Stream;

use OverflowException;

/** @internal */
final class TryingToReadFinishedStream extends OverflowException
{
    public function __construct()
    {
        parent::__construct(
            'Trying to read a finished stream.',
            1618160196
        );
    }
}
