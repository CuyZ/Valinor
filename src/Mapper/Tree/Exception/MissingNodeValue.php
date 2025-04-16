<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final class MissingNodeValue implements ErrorMessage, HasCode, HasParameters
{
    private string $body = 'Cannot be empty and must be filled with a value matching type {expected_type}.';

    private string $code = 'missing_value';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(Type $type)
    {
        $this->parameters = [
            'expected_type' => TypeHelper::dump($type),
        ];
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
