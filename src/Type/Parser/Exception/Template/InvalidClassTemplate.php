<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Template;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use LogicException;

/** @internal */
final class InvalidClassTemplate extends LogicException implements InvalidType
{
    /**
     * @param class-string $className
     */
    public function __construct(string $className, string $template, InvalidType $exception)
    {
        parent::__construct(
            "Invalid template `$template` for class `$className`: {$exception->getMessage()}",
            1630092678,
            $exception
        );
    }
}
