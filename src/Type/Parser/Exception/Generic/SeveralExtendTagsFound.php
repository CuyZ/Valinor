<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Generic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use ReflectionClass;
use RuntimeException;

/** @internal */
final class SeveralExtendTagsFound extends RuntimeException implements InvalidType
{
    /**
     * @param ReflectionClass<object> $reflection
     */
    public function __construct(ReflectionClass $reflection)
    {
        parent::__construct(
            "Only one `@extends` tag should be set for the class `$reflection->name`.",
            1670195494
        );
    }
}
