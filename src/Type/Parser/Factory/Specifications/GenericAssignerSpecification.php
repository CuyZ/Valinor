<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Type;

final class GenericAssignerSpecification
{
    /** @var array<string, Type> */
    private array $generics;

    /**
     * @param array<string, Type> $generics
     */
    public function __construct(array $generics)
    {
        $this->generics = $generics;
    }

    /**
     * @return array<string, Type>
     */
    public function generics(): array
    {
        return $this->generics;
    }
}
