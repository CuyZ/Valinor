<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Types\UnionType;
use RuntimeException;

/** @api */
final class UnionTypeDoesNotAllowNull extends RuntimeException implements Message
{
    public function __construct(UnionType $unionType)
    {
        parent::__construct(
            "Cannot assign an empty value to union type `$unionType`.",
            1618742357
        );
    }
}
