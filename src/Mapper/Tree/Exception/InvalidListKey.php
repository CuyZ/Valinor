<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\ValueDumper;

/** @internal */
final class InvalidListKey implements ErrorMessage, HasCode, HasParameters
{
    private string $body = 'Invalid sequential key {key}, expected {expected}.';

    private string $code = 'invalid_list_key';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(int|string $key, int $expected)
    {
        $this->parameters = [
            'key' => ValueDumper::dump($key),
            'expected' => (string)$expected,
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
