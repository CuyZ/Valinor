<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Definition\ClassSignature;
use LogicException;

/** @internal */
final class InvalidTypeAliasImportClass extends LogicException
{
    /**
     * @param class-string $className
     */
    public function __construct(ClassSignature $signature, string $className)
    {
        parent::__construct(
            "Cannot import a type alias from unknown class `$className` in class `{$signature->className()}`.",
            1638535486
        );
    }
}
