<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use Throwable;

/** @internal */
interface CastError extends Throwable, TranslatableMessage
{
}
