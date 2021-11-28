<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use Throwable;

interface MappingError extends Throwable
{
    /**
     * @return array<string, array<Throwable>>
     */
    public function describe(): array;
}
