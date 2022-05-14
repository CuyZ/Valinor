<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Template;

use LogicException;

/** @internal */
final class InvalidClassTemplate extends LogicException implements InvalidTemplate
{
    /**
     * @param class-string $className
     */
    public function __construct(string $className, InvalidTemplate $exception)
    {
        parent::__construct(
            "Template error for class `$className`: {$exception->getMessage()}",
            1630092678,
            $exception
        );
    }
}
