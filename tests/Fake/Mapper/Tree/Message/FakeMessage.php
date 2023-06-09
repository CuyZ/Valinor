<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Mapper\Tree\Message\Message;

final class FakeMessage implements Message, HasParameters, HasCode
{
    /** @var array<string, string> */
    private array $parameters = [];

    public function __construct(private string $body = 'some message') {}

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @param array<string, string> $parameters
     */
    public function withParameters(array $parameters): self
    {
        $clone = clone $this;
        $clone->parameters = $parameters;

        return $clone;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function code(): string
    {
        return 'some_code';
    }
}
