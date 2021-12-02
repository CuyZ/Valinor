<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Type;

final class TypeAliasAssignerSpecification
{
    /** @var array<string, Type> */
    private array $aliases;

    /**
     * @param array<string, Type> $aliases
     */
    public function __construct(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @return array<string, Type>
     */
    public function aliases(): array
    {
        return $this->aliases;
    }
}
