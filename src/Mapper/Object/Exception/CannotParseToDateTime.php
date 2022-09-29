<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

/** @internal */
final class CannotParseToDateTime extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body = 'Value {source_value} does not match any of the following formats: {formats}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param non-empty-list<non-empty-string> $formats
     */
    public function __construct(array $formats)
    {
        $this->parameters = [
            'formats' => '`' . implode('`, `', $formats) . '`',
        ];

        parent::__construct(StringFormatter::for($this), 1630686564);
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
