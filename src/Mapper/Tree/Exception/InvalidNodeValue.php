<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class InvalidNodeValue implements ErrorMessage, HasCode
{
    private string $body = 'Value {source_value} does not match {expected_signature}.';

    private string $code = 'invalid_value';

    public static function from(Type $type): ErrorMessage
    {
        if ($type instanceof ScalarType) {
            return $type->errorMessage();
        }

        return new self();
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
