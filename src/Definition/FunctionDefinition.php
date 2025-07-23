<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class FunctionDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        /** @var non-empty-string */
        public readonly string $signature,
        public readonly Attributes $attributes,
        /** @var non-empty-string|null */
        public readonly ?string $fileName,
        /** @var class-string|null */
        public readonly ?string $class,
        public readonly bool $isStatic,
        public readonly bool $isClosure,
        public readonly Parameters $parameters,
        public readonly Type $returnType
    ) {}
}
