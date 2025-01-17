<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use RuntimeException;

/** @internal */
final class SourceIsEmptyList extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(NonEmptyListType $type)
    {
        $this->parameters = [
            'expected_subtype' => TypeHelper::dump($type->subType()),
        ];

        $this->body = 'List cannot be empty and must contain values of type {expected_subtype}.';

        parent::__construct(StringFormatter::for($this), 1736015714);
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
