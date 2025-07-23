<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use Throwable;

/** @api */
interface InvalidSource extends Throwable
{
    public function source(): mixed;
}
