<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use ReflectionClass;
use RuntimeException;

/** @internal */
final class ExtendTagTypeError extends RuntimeException
{
    /**
     * @param ReflectionClass<object> $reflection
     */
    public function __construct(ReflectionClass $reflection, InvalidType $previous)
    {
        parent::__construct(
            "The `@extends` tag of the class `$reflection->name` is not valid: {$previous->getMessage()}",
            1670193574,
            $previous,
        );
    }
}
