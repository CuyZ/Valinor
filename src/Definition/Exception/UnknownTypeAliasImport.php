<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Definition\ClassSignature;
use LogicException;

final class UnknownTypeAliasImport extends LogicException
{
    /**
     * @param class-string $importClassName
     */
    public function __construct(ClassSignature $signature, string $importClassName, string $alias)
    {
        parent::__construct(
            "Type alias `$alias` imported in `{$signature->className()}` could not be found in `$importClassName`",
            1638535757
        );
    }
}
