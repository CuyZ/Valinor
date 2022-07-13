<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @internal */
final class InvalidEmptyStringValue extends RuntimeException implements CastError
{
    private string $body = 'Cannot be empty and must be filled with a valid string value.';

    public function __construct()
    {
        parent::__construct($this->body, 1632925312);
    }

    public function body(): string
    {
        return $this->body;
    }
}
