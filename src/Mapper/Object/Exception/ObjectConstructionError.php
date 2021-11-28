<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use Exception;
use RuntimeException;

final class ObjectConstructionError extends RuntimeException implements Message
{
    public function __construct(Exception $exception)
    {
        parent::__construct(
            $exception->getMessage(),
            1630142421,
            $exception
        );
    }
}
