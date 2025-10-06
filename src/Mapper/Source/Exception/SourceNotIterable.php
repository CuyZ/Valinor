<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

/** @internal */
final class SourceNotIterable extends RuntimeException implements InvalidSource
{
    public function __construct(private string $source)
    {
        parent::__construct('Invalid source, expected an iterable.');
    }

    public function source(): string
    {
        return $this->source;
    }
}
