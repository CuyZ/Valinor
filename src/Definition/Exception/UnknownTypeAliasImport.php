<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\ObjectType;
use LogicException;

/** @internal */
final class UnknownTypeAliasImport extends LogicException
{
    /**
     * @param class-string $importClassName
     */
    public function __construct(ObjectType $type, string $importClassName, string $alias)
    {
        parent::__construct(
            "Type alias `$alias` imported in `{$type->className()}` could not be found in `$importClassName`",
            1638535757
        );
    }
}
