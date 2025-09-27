<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;

/** @internal */
final class CannotResolveTypeFromUnion implements ErrorMessage, HasCode
{
    private string $body;

    private string $code = 'cannot_resolve_type_from_union';

    public function __construct(mixed $source)
    {
        if ($source === null) {
            $this->body = 'Cannot be empty and must be filled with a value matching any of {expected_signature}.';
        } else {
            $this->body = 'Value {source_value} does not match any of {expected_signature}.';
        }
    }

    public function body(): string
    {
        return $this->body;
    }

    public function code(): string
    {
        return $this->code;
    }
}
