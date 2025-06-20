<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final class SourceIsEmptyArray implements ErrorMessage, HasCode, HasParameters
{
    private string $body = 'Array cannot be empty and must contain values of type {expected_subtype}.';

    private string $code = 'value_is_empty_array';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(NonEmptyArrayType $type)
    {
        $this->parameters = [
            'expected_subtype' => TypeHelper::dump($type->subType()),
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
