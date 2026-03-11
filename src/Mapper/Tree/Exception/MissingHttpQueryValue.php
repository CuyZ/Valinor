<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;

final class MissingHttpQueryValue implements ErrorMessage, HasCode, HasParameters
{
    public function __construct(
        private string|int $key,

    ) {}

    public function code(): string
    {
        return 'missing_http_query_value';
    }

    public function body(): string
    {
        return 'The query parameter `{key}` is missing.';
    }

    public function parameters(): array
    {
        return [
            'key' => $this->key,
        ];
    }
}
