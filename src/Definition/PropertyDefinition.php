<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class PropertyDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $signature,
        public readonly Type $type,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly bool $isPublic,
        public readonly Attributes $attributes
    ) {}
}
