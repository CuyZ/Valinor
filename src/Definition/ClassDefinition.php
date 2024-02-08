<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\ClassType;

/** @internal */
final class ClassDefinition
{
    public function __construct(
        /** @var class-string */
        public readonly string $name,
        public readonly ClassType $type,
        public readonly Attributes $attributes,
        public readonly Properties $properties,
        public readonly Methods $methods,
        public readonly bool $isFinal,
        public readonly bool $isAbstract,
    ) {}
}
