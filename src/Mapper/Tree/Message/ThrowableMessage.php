<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use RuntimeException;
use Throwable;

/** @api */
final class ThrowableMessage extends RuntimeException implements ErrorMessage, HasCode
{
    public static function new(string $message, string $code): self
    {
        $instance = new self($message);
        $instance->code = $code;

        return $instance;
    }

    public static function from(Throwable $message): self
    {
        // @infection-ignore-all
        $instance = new self($message->getMessage(), 0, $message);
        $instance->code = (string)$message->getCode();

        return $instance;
    }

    public function body(): string
    {
        return $this->message;
    }

    public function code(): string
    {
        return (string)$this->code;
    }
}
