<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final readonly class PropertyDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        /** @var non-empty-string */
        public string $signature,
        public Type $type,
        public Type $nativeType,
        public bool $hasDefaultValue,
        public mixed $defaultValue,
        public bool $isPublic,
        public Attributes $attributes
    ) {}
}
