<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use Exception;

final class FakeErrorMessage extends Exception implements Message
{
    public function __construct(string $message = 'some error message')
    {
        parent::__construct($message);
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
