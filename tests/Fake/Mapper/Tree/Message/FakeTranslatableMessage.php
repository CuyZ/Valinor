<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;

final class FakeTranslatableMessage implements TranslatableMessage, HasCode
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param array<string, string> $parameters
     */
    public function __construct(string $body = 'some message', array $parameters = [])
    {
        $this->body = $body;
        $this->parameters = $parameters;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function code(): string
    {
        return '1652902453';
    }

    public function __toString()
    {
        return "$this->body (toString)";
    }
}
