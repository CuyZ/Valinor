<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

/** @internal */
final class InvalidIntegerRangeValue extends RuntimeException implements CastError, HasParameters
{
    private string $body = 'Invalid value {value}: it must be an integer between {min} and {max}.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(int $value, IntegerRangeType $type)
    {
        $this->parameters = [
            'value' => (string)$value,
            'min' => (string)$type->min(),
            'max' => (string)$type->max(),
        ];

        parent::__construct(StringFormatter::for($this), 1638785150);
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
