<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

/** @api */
final class InvalidPositiveIntegerValue extends RuntimeException implements CastError
{
    private string $body = 'Invalid value {value}: it must be a positive integer.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(int $value)
    {
        $this->parameters = [
            'value' => (string)$value,
        ];

        parent::__construct(StringFormatter::for($this), 1632923676);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
