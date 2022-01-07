<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use Stringable;

/** @api */
interface Type extends Stringable
{
    /**
     * @param mixed $value
     */
    public function accepts($value): bool;

    public function matches(self $other): bool;
}
