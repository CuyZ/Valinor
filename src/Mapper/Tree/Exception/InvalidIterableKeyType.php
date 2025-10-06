<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

use function get_debug_type;

/** @internal */
final class InvalidIterableKeyType extends RuntimeException
{
    public function __construct(mixed $key, string $path)
    {
        $type = get_debug_type($key);

        parent::__construct(
            "Invalid key of type `$type` at path `$path`, only integers and strings are allowed.",
        );
    }
}
