<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use Throwable;

interface CastError extends Throwable, Message
{
}
