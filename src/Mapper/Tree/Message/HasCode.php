<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/**
 * This interface can be implemented by a message to help to identify it with a
 * unique code.
 */
interface HasCode extends Message
{
    public function code(): string;
}
