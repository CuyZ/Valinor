<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use RuntimeException;

/** @internal */
final class MissingNodeValue extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body = 'Cannot be empty and must be filled with a value matching type {expected_type}.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(Type $type)
    {
        $this->parameters = [
            'expected_type' => TypeHelper::dump($type),
        ];

        parent::__construct(StringFormatter::for($this), 1655449641);
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
