<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

/** @internal */
final readonly class FunctionObject
{
    public function __construct(
        public FunctionDefinition $definition,
        /** @var callable */
        public mixed $callback,
    ) {}
}
