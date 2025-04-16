<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final class SourceMustBeIterable implements ErrorMessage, HasCode, HasParameters
{
    private string $body;

    private string $code = 'value_is_not_iterable';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(mixed $value, Type $type)
    {
        $this->parameters = [
            'expected_type' => TypeHelper::dump($type),
        ];

        if ($value === null) {
            $this->body = TypeHelper::containsObject($type)
                ? 'Cannot be empty.'
                : 'Cannot be empty and must be filled with a value matching type {expected_type}.';
        } else {
            $this->body = TypeHelper::containsObject($type)
                ? 'Invalid value {source_value}.'
                : 'Value {source_value} does not match type {expected_type}.';
        }
    }

    public function body(): string
    {
        return $this->body;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
