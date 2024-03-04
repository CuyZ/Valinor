<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class ParameterDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        /** @var non-empty-string */
        public readonly string $signature,
        public readonly Type $type,
        public readonly Type $nativeType,
        public readonly bool $isOptional,
        public readonly bool $isVariadic,
        public readonly mixed $defaultValue,
        public readonly Attributes $attributes
    ) {}
}
