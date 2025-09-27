<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;

/** @internal */
final class SourceIsEmptyArray implements ErrorMessage, HasCode
{
    private string $body = 'Cannot be empty and must be filled with a value matching {expected_signature}.';

    private string $code = 'value_is_empty_array';

    public function body(): string
    {
        return $this->body;
    }

    public function code(): string
    {
        return $this->code;
    }
}
