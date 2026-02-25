<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final readonly class MethodDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        /** @var non-empty-string */
        public string $signature,
        public Attributes $attributes,
        public Parameters $parameters,
        public bool $isStatic,
        public bool $isPublic,
        public Type $returnType
    ) {}
}
