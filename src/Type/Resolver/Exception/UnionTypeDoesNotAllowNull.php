<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\TypeHelper;
use RuntimeException;

/** @api */
final class UnionTypeDoesNotAllowNull extends RuntimeException implements Message
{
    public function __construct(UnionType $unionType)
    {
        $message = TypeHelper::containsObject($unionType)
            ? 'Cannot be empty.'
            : "Cannot be empty and must be filled with a value matching `$unionType`.";

        parent::__construct($message, 1618742357);
    }
}
