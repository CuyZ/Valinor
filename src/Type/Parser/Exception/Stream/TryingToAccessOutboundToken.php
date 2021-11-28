<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Stream;

use OutOfBoundsException;

final class TryingToAccessOutboundToken extends OutOfBoundsException
{
    public function __construct()
    {
        parent::__construct(
            'Trying to access outbound token.',
            1618160479
        );
    }
}
