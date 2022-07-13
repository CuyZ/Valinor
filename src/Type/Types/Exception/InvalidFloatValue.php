<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

/** @internal */
final class InvalidFloatValue extends RuntimeException implements CastError, HasParameters
{
    private string $body = 'Value {value} does not match expected {expected_value}.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(float $value, float $expected)
    {
        $this->parameters = [
            'value' => (string)$value,
            'expected_value' => (string)$expected,
        ];

        parent::__construct(StringFormatter::for($this), 1652110115);
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
