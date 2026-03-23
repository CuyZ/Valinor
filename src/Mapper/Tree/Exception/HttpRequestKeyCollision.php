<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;

/** @internal */
final class HttpRequestKeyCollision implements ErrorMessage, HasCode, HasParameters
{
    public function __construct(
        private string $key,
    ) {}

    public function body(): string
    {
        return 'Key `{key}` was found in several HTTP request sources. It must be sent in only one of route, query or body.';
    }

    public function code(): string
    {
        return 'key_collision';
    }

    public function parameters(): array
    {
        return [
            'key' => $this->key,
        ];
    }
}
