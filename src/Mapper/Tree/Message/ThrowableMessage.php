<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use RuntimeException;

final class ThrowableMessage extends RuntimeException implements Message, HasCode
{
    public function __construct(string $message, string $code)
    {
        parent::__construct($message);

        $this->code = $code;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
