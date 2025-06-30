<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/** @api */
interface Message
{
    /** @pure */
    public function body(): string;
}
