<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

/** @internal */
final class InvalidEmptyStringValue extends RuntimeException implements CastError
{
    private string $body = 'Cannot be empty and must be filled with a valid string value.';

    public function __construct()
    {
        parent::__construct(StringFormatter::for($this), 1632925312);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function parameters(): array
    {
        return [];
    }
}
