<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class MethodDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        /** @var non-empty-string */
        public readonly string $signature,
        public readonly Attributes $attributes,
        public readonly Parameters $parameters,
        public readonly bool $isStatic,
        public readonly bool $isPublic,
        public readonly Type $returnType
    ) {}
}
