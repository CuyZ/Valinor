<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface VacantType extends Type
{
    public function symbol(): string;
}
