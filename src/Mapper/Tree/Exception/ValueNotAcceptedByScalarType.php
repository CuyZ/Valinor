<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class ValueNotAcceptedByScalarType extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body;

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

        $this->body = $value === null
            ? 'Cannot be empty and must be filled with a value matching type {expected_type}.'
            : 'Value {value} does not match type {expected_type}.';

        parent::__construct(StringFormatter::for($this), 1655030601);
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
