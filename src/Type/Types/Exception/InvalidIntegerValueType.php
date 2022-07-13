<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class InvalidIntegerValueType extends RuntimeException implements CastError, HasParameters
{
    private string $body = 'Value {value} does not match integer value {expected_value}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param mixed $value
     */
    public function __construct($value, int $expected)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($value),
            'expected_value' => (string)$expected,
        ];

        parent::__construct(StringFormatter::for($this), 1631267159);
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
