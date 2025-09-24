<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;

/** @internal */
final class TooManyResolvedTypesFromUnion implements ErrorMessage, HasCode
{
    private string $body = 'Invalid value {source_value}, cannot take a decision because it matches two or more types from {expected_signature}.';

    private string $code = 'too_many_resolved_types_from_union';

    public function body(): string
    {
        return $this->body;
    }

    public function code(): string
    {
        return $this->code;
    }
}
