<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

use Throwable;

/** @api */
interface ErrorMessage extends Message, Throwable
{
}
