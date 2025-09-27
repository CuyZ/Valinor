<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;

/** @internal */
final class SourceMustBeIterable implements ErrorMessage, HasCode
{
    private string $body;

    private string $code = 'value_is_not_iterable';

    public function __construct(mixed $value)
    {
        if ($value === null) {
            $this->body = 'Cannot be empty and must be filled with a value matching {expected_signature}.';
        } else {
            $this->body = 'Value {source_value} does not match {expected_signature}.';
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
