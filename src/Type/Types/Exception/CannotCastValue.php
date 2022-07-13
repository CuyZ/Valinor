<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class CannotCastValue extends RuntimeException implements CastError, HasParameters
{
    private string $body = 'Cannot cast {value} to {expected_type}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param mixed $value
     */
    public function __construct($value, ScalarType $type)
    {
        $this->parameters = [
            'value' => ValueDumper::dump($value),
            'expected_type' => TypeHelper::dump($type),
        ];

        parent::__construct(StringFormatter::for($this), 1603216198);
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
