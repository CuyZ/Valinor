<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\Message;

final class FakeMessage implements Message
{
    public function __toString(): string
    {
        return 'some message';
    }
}
