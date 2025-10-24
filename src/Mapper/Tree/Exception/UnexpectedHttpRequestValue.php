<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;

/** @internal */
final class UnexpectedHttpRequestValue implements ErrorMessage, HasCode, HasParameters
{
    private string $body;

    private string $code;

    /** @var array<string, string> */
    private array $parameters;

    private function __construct(string $key)
    {
        $this->parameters = [
            'key' => "`$key`",
        ];
    }

    public static function forRequestBodyValue(string $key): self
    {
        $self = new self($key);
        $self->body = 'Unexpected body value {key}.';
        $self->code = 'unexpected_http_request_body_value';

        return $self;
    }

    public static function forRequestQueryParameter(string $key): self
    {
        $self = new self($key);
        $self->body = 'Unexpected query parameter {key}.';
        $self->code = 'unexpected_http_request_query_parameter';

        return $self;
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
