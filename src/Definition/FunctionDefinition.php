<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class FunctionDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $signature,
        public readonly Attributes $attributes,
        public readonly ?string $fileName,
        /** @var class-string|null */
        public readonly ?string $class,
        public readonly bool $isStatic,
        public readonly bool $isClosure,
        public readonly Parameters $parameters,
        public readonly Type $returnType
    ) {}
}
