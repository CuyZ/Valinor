<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use RuntimeException;

/** @internal */
final class InvalidSource extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(mixed $source, Arguments $arguments)
    {
        $this->parameters = [
            'expected_type' => TypeHelper::dumpArguments($arguments),
        ];

        $this->body = $source === null
            ? 'Cannot be empty and must be filled with a value matching type {expected_type}.'
            : 'Value {source_value} does not match type {expected_type}.';

        parent::__construct(StringFormatter::for($this), 1632903281);
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
