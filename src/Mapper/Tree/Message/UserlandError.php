<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use RuntimeException;
use Throwable;

/** @internal */
final class UserlandError extends RuntimeException implements ErrorMessage
{
    /**
     * PHP8.1 intersection
     * @return Message&Throwable
     */
    public static function from(Throwable $message): Message
    {
        // @infection-ignore-all
        return $message instanceof Message
            ? $message
            : new self('Invalid value.', 1657215570, $message);
    }

    public function body(): string
    {
        return 'Invalid value.';
    }

    public function previous(): Throwable
    {
        return $this->getPrevious(); // @phpstan-ignore-line
    }
}
