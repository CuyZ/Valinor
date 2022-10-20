<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class TypeAliasAssignerSpecification
{
    public function __construct(
        /** @var array<string, Type> */
        private array $aliases
    ) {
    }

    /**
     * @return array<string, Type>
     */
    public function aliases(): array
    {
        return $this->aliases;
    }
}
