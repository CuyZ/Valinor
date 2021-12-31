<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use RuntimeException;
use Throwable;

final class ThrowableMessage extends RuntimeException implements Message, HasCode
{
    public static function new(string $message, string $code): self
    {
        $instance = new self($message);
        $instance->code = $code;

        return $instance;
    }

    /**
     * @return Message&Throwable
     */
    public static function from(Throwable $message): Message
    {
        if ($message instanceof Message) {
            return $message;
        }

        /** @infection-ignore-all */
        $instance = new self($message->getMessage(), 0, $message);
        $instance->code = (string)$message->getCode();

        return $instance;
    }

    public function code(): string
    {
        return (string)$this->code;
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
