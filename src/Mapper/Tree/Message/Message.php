<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/** @api */
interface Message
{
    public function body(): string;
}
