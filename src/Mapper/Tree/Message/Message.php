<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/** @api */
interface Message
{
    /**
     * @psalm-mutation-free
     */
    public function body(): string;
}
