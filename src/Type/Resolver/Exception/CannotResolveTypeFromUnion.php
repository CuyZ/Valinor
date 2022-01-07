<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Types\UnionType;
use RuntimeException;

use function get_debug_type;

/** @api */
final class CannotResolveTypeFromUnion extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct(UnionType $unionType, $value)
    {
        $type = get_debug_type($value);

        parent::__construct(
            "Impossible to resolve the type from the union `$unionType` with a value of type `$type`.",
            1607027306
        );
    }
}
