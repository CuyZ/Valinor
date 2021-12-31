<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\Message;

final class FakeMessage implements Message, HasCode
{
    private string $message;

    public function __construct(string $message = 'some message')
    {
        $this->message = $message;
    }

    public function __toString(): string
    {
        return $this->message;
    }

    public function code(): string
    {
        return 'some_code';
    }
}
