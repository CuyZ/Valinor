<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class InvalidListKey extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body = 'Invalid sequential key {key}, expected {expected}.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(int|string $key, int $expected)
    {
        $this->parameters = [
            'key' => ValueDumper::dump($key),
            'expected' => (string)$expected,
        ];

        parent::__construct(StringFormatter::for($this), 1654273010);
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
