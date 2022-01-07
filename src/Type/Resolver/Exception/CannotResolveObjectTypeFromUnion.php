<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Types\UnionType;
use RuntimeException;

/** @api */
final class CannotResolveObjectTypeFromUnion extends RuntimeException implements Message
{
    public function __construct(UnionType $unionType)
    {
        parent::__construct(
            "Impossible to resolve the object type from the union `$unionType`.",
            1641406600
        );
    }
}
