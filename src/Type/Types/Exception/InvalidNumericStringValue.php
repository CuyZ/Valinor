<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

/** @internal */
final class InvalidNumericStringValue extends RuntimeException implements CastError
{
    private string $body = 'Invalid value {value}: it must be a numeric.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(string $value)
    {
        $this->parameters = [
            'value' => $value,
        ];

        parent::__construct(StringFormatter::for($this), 1632923705);
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
