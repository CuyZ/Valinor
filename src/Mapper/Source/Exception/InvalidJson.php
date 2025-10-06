<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use JsonException;
use RuntimeException;

/** @internal */
final class InvalidJson extends RuntimeException implements InvalidSource
{
    public function __construct(private string $source, ?JsonException $previous = null)
    {
        parent::__construct('Invalid JSON source.', previous: $previous);
    }

    public function source(): string
    {
        return $this->source;
    }
}
