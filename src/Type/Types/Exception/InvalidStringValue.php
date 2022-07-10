<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class InvalidStringValue extends RuntimeException implements CastError
{
    private string $body = 'Value {value} does not match expected {expected_value}.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(string $value, string $expected)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($value),
            'expected_value' => ValueDumper::dump($expected),
        ];

        parent::__construct(StringFormatter::for($this), 1631263740);
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
