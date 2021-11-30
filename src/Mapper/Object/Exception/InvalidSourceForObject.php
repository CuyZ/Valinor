<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;

use function get_debug_type;

final class InvalidSourceForObject extends RuntimeException implements Message
{
    /**
     * @param mixed $source
     */
    public function __construct($source)
    {
        $type = get_debug_type($source);

        parent::__construct(
            "Invalid source type `$type`, it must be an iterable.",
            1632903281
        );
    }
}
