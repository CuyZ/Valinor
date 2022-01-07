<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source;

/** @api */
interface IdentifiableSource
{
    public function sourceName(): string;
}
