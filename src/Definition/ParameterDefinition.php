<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class ParameterDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $signature,
        public readonly Type $type,
        public readonly bool $isOptional,
        public readonly bool $isVariadic,
        public readonly mixed $defaultValue,
        public readonly Attributes $attributes
    ) {}
}
