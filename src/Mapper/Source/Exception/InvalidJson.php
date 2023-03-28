<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

/** @internal */
final class InvalidJson extends RuntimeException implements InvalidSource
{
    public function __construct(private string $source)
    {
        parent::__construct(
            'Invalid JSON source.',
            1566307185
        );
    }

    public function source(): string
    {
        return $this->source;
    }
}
