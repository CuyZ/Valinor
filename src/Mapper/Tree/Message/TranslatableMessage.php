<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/** @api */
interface TranslatableMessage extends Message
{
    public function body(): string;

    /**
     * @return array<string, string>
     */
    public function parameters(): array;
}
